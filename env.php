<?php
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
$db = $_ENV['DB_NAME'];;
$user = $_ENV['DB_USER'];
$pass = $_ENV['DB_PASS'];
$jwtKey = $_ENV['JWT_KEY'];
$baseURL = $_ENV['baseURL'];


date_default_timezone_set('Asia/Jakarta');
//error_reporting(0);
$msg = new \Plasticbrain\FlashMessages\FlashMessages();
$msg->setCssClassMap([

    $msg::INFO => 'alert alert-info text-dark  alert-dismissible fade show mb-4',
    $msg::SUCCESS => 'alert alert-success text-dark alert-dismissible fade show mb-4',
    $msg::WARNING => 'alert alert-warning text-dark alert-dismissible fade show  mb-4',
    $msg::ERROR => 'alert alert-danger  text-dark alert-dismissible fade show mb-4',
]);

// $msg->setMsgWrapper("<div class='%s mt-3' role='alert' style='width: 30vw;position: absolute;margin-left:1vw;'>
// <strong class=''>%s</strong>

// </div>");

// $msg->setCloseBtn('  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
// <span aria-hidden="true">&times;</span>
// <div class="alert alert-success alert-dismissible fade show" role="alert">
//     <span class="alert-icon"><i class="ni ni-like-2"></i></span>
//     <span class="alert-text"><strong>Success!</strong> This is a success alert—check it out!</span>

// </div>

// ALTER TABLE `peneliti_asing` ADD `created_at` TIMESTAMP NULL COMMENT 'Created_at' , ADD `updated_at` TIMESTAMP NULL COMMENT 'Updated_at' AFTER `created_at`, ADD `oleh` INT NULL COMMENT 'By' AFTER `updated_at`;
