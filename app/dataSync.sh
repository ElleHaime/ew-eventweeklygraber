#!/bin/bash

SCRIPTS=($1)

for SCRIPT_NAME in ${SCRIPTS[*]}
do
	FULL_PATH="$PWD/$SCRIPT_NAME"
	php $FULL_PATH 2>/dev/null &
done
exit 0

