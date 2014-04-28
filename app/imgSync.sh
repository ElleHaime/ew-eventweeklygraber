#!/bin/bash

cd ../upload/event

SYNCLOG='$PWD/logs/sync.log'
TARGET_DIR='/var/www/EventWeeklyGraber/upload/event'
DEST_DIR='/var/www/EventWeekly/public/upload/test2/'
DEST_HOST='127.0.0.1'

echo $(date +'%F %T') ': starting synchronization images with' $DEST_HOST
rsync --progress --stats --partial --ignore-existing -rv $TARGET_DIR $DEST_DIR > /var/log/EventWeeklyGraber/logs/sync.log
