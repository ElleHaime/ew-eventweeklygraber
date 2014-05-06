#!/bin/bash

cd ../upload/event

SYNCLOG='$PWD/logs/sync.log'
TARGET_DIR='/var/www/EventWeeklyGraber/upload/event/'
DEST_DIR='/var/www/EventWeekly/public/upload/img/event'
DEST_HOST='172.31.37.161'
DEST_USER='eventdev'

rsync --progress --stats --partial --ignore-existing -rv $TARGET_DIR $DEST_DIR > /var/www/EventWeeklyGraber/logs/sync.log
