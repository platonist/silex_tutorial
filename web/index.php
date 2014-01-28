<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'    => 'pdo_mysql',
        'host'      => 'localhost',
        'dbname'    => 'silex_tutorial',
        'user'      => 'root',
        'password'  => '',
        'charset'   => 'utf8',
    ),
));
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'translator.messages' => array(),
));
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

$app->get('/user/list', function() use ($app) {
    $users = $app['db']->fetchAll('SELECT * FROM user');
    
    return $app['twig']->render('user/list.twig', array(
        'users' => $users
    ));
})
->bind('user_list');

$app->match('/user/add', function(Symfony\Component\HttpFoundation\Request $request) use ($app) {
    $form = $app['form.factory']->createBuilder('form')
                ->add('first_name', 'text', array(
                    'label' => 'First Name'
                ))
                ->add('last_name', 'text', array(
                    'label' => 'Last Name'
                ))
                ->add('email', 'text', array(
                    'label' => 'Email'
                ))
                ->add('save', 'submit')
                ->getForm();
    
    $form->handleRequest($request);
    
    if ($form->isValid()) {
        $data = $form->getData();
        
        $app['db']->insert('user', array(
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
        ));
        
        return $app->redirect($app['url_generator']->generate('user_list'));
    }
    
    return $app['twig']->render('user/add.twig', array(
        'form' => $form->createView()
    ));
})
->bind('user_add');

$app->match('/user/edit/{id}', function(Symfony\Component\HttpFoundation\Request $request, $id) use ($app) {
    $user = $app['db']->fetchAssoc('SELECT * FROM user WHERE id = ?', array((int) $id));
    
    $form = $app['form.factory']->createBuilder('form', $user)
                ->add('first_name', 'text', array(
                    'label' => 'First Name'
                ))
                ->add('last_name', 'text', array(
                    'label' => 'Last Name'
                ))
                ->add('email', 'text', array(
                    'label' => 'Email'
                ))
                ->add('save', 'submit')
                ->getForm();
    
    $form->handleRequest($request);
    
    if ($form->isValid()) {
        $data = $form->getData();
        
        $app['db']->update('user', array(
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
        ), array('id' => $id));
        
        return $app->redirect($app['url_generator']->generate('user_list'));
    }
    
    return $app['twig']->render('user/edit.twig', array(
        'form' => $form->createView()
    ));
})
->bind('user_add');

$app->run();