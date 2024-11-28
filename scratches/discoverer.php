<?php

use App\Traits\QuerySelector;
use Symfony\Component\DomCrawler\Crawler;

require_once dirname(__DIR__).'/vendor/autoload.php';


$finder = new QuerySelector();

$finder
    ->addSelector('//*[@id="main"]//section//div[@class="layout-box__content"]')
    ->addSelector('*/div[@class="layout-box__card"]/div[@class="product"]');

$crawler = $finder->select(new Crawler(file_get_contents('https://goldi.ua/catalog/zinocij-odag')));

var_dump($crawler);