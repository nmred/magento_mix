#!/bin/bash

INSTALL_PAGH="/data0/cron"
rsync -av --delete . $INSTALL_PAGH
cp /data0/app.ini  $INSTALL_PAGH/crontab/orderAuto/
cp magento_cron /etc/cron.d/
