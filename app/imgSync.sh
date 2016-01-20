#!/bin/bash

TARGET_DIR='/home/eventcron/www/EventWeeklyGraber/upload/event/'
DEST_DIR='/home/eventprod/www/EventWeekly/public/upload/img/event'
DEST_HOST='172.31.37.161'
DEST_USER='eventprod'

echo $(date +'%F %T') ': starting synchronization images with' $DEST_HOST
rsync --progress --stats --partial --ignore-errors --ignore-existing -rp $TARGET_DIR $DEST_USER@$DEST_HOST:$DEST_DIR