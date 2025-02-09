<?php
namespace App\Controllers;

use Core\Controller;

class MappingController extends Controller {
    /**
     * Display the mapping configuration page
     */
    public function indexAction() {
        $this->render('mapping/index.php', ['title' => 'Mapping Configuration']);
    }
}
