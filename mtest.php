<?php

require 'app/Mage.php';

Mage::app('admin');

Mage::getSingleton('mailchimp/cron')->syncBatchData(null);
