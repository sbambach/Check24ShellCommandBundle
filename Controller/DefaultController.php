<?php

namespace Shopping\ShellCommandBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('ShellCommandBundle:Default:index.html.twig');
    }
}
