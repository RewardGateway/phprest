<?php


$app->get('/{version:2\.\d}/helper/lookup/scheme', '\API\v2\Public\Controller\Helper::findSchemesForEmail');
