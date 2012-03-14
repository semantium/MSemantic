#!/bin/bash
FILE='mysql4-upgrade-0.8.5-0.8.6.php'

for (( i=6;i<10;i++)); do
    let j=$i+1
    NEWFILE=mysql4-upgrade-0.8.$i-0.8.$j.php
    cp $FILE $NEWFILE
    echo $NEWFILE generated
done 
