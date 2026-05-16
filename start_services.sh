#!/bin/bash
# ============================================================
# BeachWatch — Service Automation Script
# Automates startup, XML processing, and system monitoring
# Usage: bash start_services.sh [start|stop|status|process-xml]
# ============================================================

PROJECT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
LOG_FILE="$PROJECT_DIR/logs/beachwatch.log"
RABBITMQ_CONSUMER="$PROJECT_DIR/php/rabbitmq_receive.php"
XML_DIR="$PROJECT_DIR/xml"
BACKUP_DIR="$PROJECT_DIR/xml/backups"

# Colors for terminal output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Create directories if missing
mkdir -p "$PROJECT_DIR/logs"
mkdir -p "$BACKUP_DIR"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

print_header() {
    echo -e "${CYAN}"
    echo "╔══════════════════════════════════════════╗"
    echo "║        BeachWatch Service Manager        ║"
    echo "║   Beach Resort Reservation System        ║"
    echo "╚══════════════════════════════════════════╝"
    echo -e "${NC}"
}

# ============================================================
# Check if a process is running
# ============================================================
is_running() {
    pgrep -f "$1" > /dev/null 2>&1
}

# ============================================================
# Start RabbitMQ service
# ============================================================
start_rabbitmq() {
    echo -e "${YELLOW}Starting RabbitMQ...${NC}"
    if is_running "rabbitmq"; then
        echo -e "${GREEN}RabbitMQ is already running.${NC}"
        log "RabbitMQ already running."
    else
        # Windows (Git Bash): call the RabbitMQ sbin script
        if command -v rabbitmq-server.bat &> /dev/null; then
            rabbitmq-server.bat start &
        elif command -v rabbitmq-server &> /dev/null; then
            rabbitmq-server -detached
        else
            echo -e "${RED}RabbitMQ not found in PATH. Please start it manually.${NC}"
            log "ERROR: RabbitMQ not in PATH."
            return 1
        fi
        sleep 3
        echo -e "${GREEN}RabbitMQ started.${NC}"
        log "RabbitMQ started."
    fi
}

# ============================================================
# Start the PHP RabbitMQ consumer in background
# ============================================================
start_consumer() {
    echo -e "${YELLOW}Starting BeachWatch notification consumer...${NC}"
    if is_running "rabbitmq_receive.php"; then
        echo -e "${GREEN}Consumer already running.${NC}"
    else
        nohup php "$RABBITMQ_CONSUMER" >> "$LOG_FILE" 2>&1 &
        CONSUMER_PID=$!
        echo "$CONSUMER_PID" > "$PROJECT_DIR/logs/consumer.pid"
        echo -e "${GREEN}Consumer started (PID: $CONSUMER_PID).${NC}"
        log "Consumer started (PID: $CONSUMER_PID)."
    fi
}

# ============================================================
# Stop the consumer
# ============================================================
stop_consumer() {
    echo -e "${YELLOW}Stopping consumer...${NC}"
    PID_FILE="$PROJECT_DIR/logs/consumer.pid"
    if [ -f "$PID_FILE" ]; then
        PID=$(cat "$PID_FILE")
        kill "$PID" 2>/dev/null && echo -e "${GREEN}Consumer stopped.${NC}" || echo -e "${RED}Could not stop consumer.${NC}"
        rm -f "$PID_FILE"
        log "Consumer stopped (PID: $PID)."
    else
        pkill -f "rabbitmq_receive.php" 2>/dev/null
        echo -e "${YELLOW}Consumer stopped (no PID file found).${NC}"
    fi
}

# ============================================================
# Backup XML files
# ============================================================
backup_xml() {
    echo -e "${YELLOW}Backing up XML files...${NC}"
    TIMESTAMP=$(date '+%Y%m%d_%H%M%S')
    for xmlfile in "$XML_DIR"/*.xml; do
        if [ -f "$xmlfile" ]; then
            BASENAME=$(basename "$xmlfile")
            cp "$xmlfile" "$BACKUP_DIR/${BASENAME%.xml}_$TIMESTAMP.xml"
            echo -e "  ${GREEN}✓ Backed up: $BASENAME${NC}"
            log "Backed up: $BASENAME → ${BASENAME%.xml}_$TIMESTAMP.xml"
        fi
    done
    echo -e "${GREEN}XML backup complete.${NC}"
}

# ============================================================
# Process XML — validate and count records
# ============================================================
process_xml() {
    echo -e "${CYAN}Processing XML files...${NC}"
    for xmlfile in "$XML_DIR"/*.xml; do
        if [ -f "$xmlfile" ]; then
            BASENAME=$(basename "$xmlfile")
            # Validate XML with xmllint if available
            if command -v xmllint &> /dev/null; then
                if xmllint --noout "$xmlfile" 2>/dev/null; then
                    RECORDS=$(grep -c "<resort\|<reservation" "$xmlfile" 2>/dev/null || echo "?")
                    echo -e "  ${GREEN}✓ $BASENAME — Valid XML ($RECORDS records)${NC}"
                    log "XML valid: $BASENAME"
                else
                    echo -e "  ${RED}✗ $BASENAME — INVALID XML!${NC}"
                    log "ERROR: Invalid XML: $BASENAME"
                fi
            else
                echo -e "  ${YELLOW}⚠ $BASENAME — xmllint not installed, skipping validation${NC}"
            fi
        fi
    done
}

# ============================================================
# System status check
# ============================================================
status() {
    echo -e "${CYAN}BeachWatch System Status${NC}"
    echo "──────────────────────────────"

    # RabbitMQ
    if is_running "rabbitmq"; then
        echo -e "  RabbitMQ       : ${GREEN}RUNNING${NC}"
    else
        echo -e "  RabbitMQ       : ${RED}STOPPED${NC}"
    fi

    # Consumer
    if is_running "rabbitmq_receive.php"; then
        echo -e "  Notification   : ${GREEN}RUNNING${NC}"
    else
        echo -e "  Notification   : ${RED}STOPPED${NC}"
    fi

    # Apache (XAMPP)
    if is_running "httpd" || is_running "apache"; then
        echo -e "  Apache/XAMPP   : ${GREEN}RUNNING${NC}"
    else
        echo -e "  Apache/XAMPP   : ${RED}STOPPED${NC}"
    fi

    # MySQL
    if is_running "mysqld"; then
        echo -e "  MySQL          : ${GREEN}RUNNING${NC}"
    else
        echo -e "  MySQL          : ${RED}STOPPED${NC}"
    fi

    echo "──────────────────────────────"

    # XML file sizes
    echo -e "${CYAN}XML Files:${NC}"
    for xmlfile in "$XML_DIR"/*.xml; do
        if [ -f "$xmlfile" ]; then
            SIZE=$(wc -c < "$xmlfile")
            echo -e "  $(basename "$xmlfile") — $SIZE bytes"
        fi
    done

    echo ""
    log "Status check performed."
}

# ============================================================
# Main entrypoint
# ============================================================
print_header

COMMAND="${1:-start}"

case "$COMMAND" in
    start)
        log "=== BeachWatch startup ==="
        backup_xml
        start_rabbitmq
        start_consumer
        process_xml
        echo ""
        echo -e "${GREEN}BeachWatch is ready! Open http://localhost/beachwatch in your browser.${NC}"
        log "Startup complete."
        ;;
    stop)
        stop_consumer
        echo -e "${GREEN}Services stopped.${NC}"
        log "Services stopped."
        ;;
    status)
        status
        ;;
    process-xml)
        backup_xml
        process_xml
        ;;
    restart)
        stop_consumer
        sleep 2
        start_consumer
        echo -e "${GREEN}Consumer restarted.${NC}"
        log "Consumer restarted."
        ;;
    *)
        echo "Usage: bash start_services.sh [start|stop|status|process-xml|restart]"
        ;;
esac
