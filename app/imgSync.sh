#!/bin/bash

cd ../upload/event

SYNCLOG='$PWD/logs/sync.log'
TARGET_DIR='/home/eventcron/www/EventWeeklyGraber/upload/event/'
DEST_DIR='/home/eventdev/www/EventWeekly/public/upload/img/event'
DEST_HOST='172.31.37.161'
DEST_USER='eventdev'

echo $(date +'%F %T') ': starting synchronization images with' $DEST_HOST
rsync --progress --stats --partial --ignore-existing -rv $TARGET_DIR $DEST_USER@$DEST_HOST:$DEST_DIR > /home/eventcron/www/EventWeeklyGraber/logs/sync.log
