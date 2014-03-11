#!/bin/bash

SCRIPTS=($1 $2)

check_process() {
	if [ "$1" = "" ];
    then
            return 0
    fi

    PROCESS_NUM=$(ps -ef | grep "$1" | grep -v "grep" | grep -v "$$" | wc -l)
    if [ $PROCESS_NUM -eq 2 ];
    then
    	return 1
    else 
    	return 0
    fi
}

for SCRIPT_NAME in ${SCRIPTS[*]}
do
	FULL_PATH="$PWD/$SCRIPT_NAME"
	echo $FULL_PATH
	check_process $SCRIPT_NAME
	CHECK_ANS=$?

	if [ $CHECK_ANS -eq 0 ];
	then
		php $FULL_PATH 2>/dev/null &
	fi
done
exit 0





