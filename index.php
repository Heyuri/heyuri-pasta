<?php

// start the session first
session_start();

use Puchiko\request\request;

require_once __DIR__ . '/vendor/autoload.php';

// init application context for routes
$request       = request::fromGlobals();
$routerContext = new routeContext($request);

// init route handler
$routeHandler = new routeHandler($routerContext);

// register the routes
$routeHandler->addRoute("mainRoute",      __DIR__ . "/source/routes/mainRoute.php");
$routeHandler->addRoute("newPasta",       __DIR__ . "/source/routes/newPastaRoute.php");
$routeHandler->addRoute("viewPasta",      __DIR__ . "/source/routes/viewPasta.php");
$routeHandler->addRoute("createPasta",    __DIR__ . "/source/routes/createPastaRoute.php");
$routeHandler->addRoute("moderateRoute",  __DIR__ . "/source/routes/moderateRoute.php");

// get the route page
$routeLabel = $request->getParameter('route', null, 'mainRoute');

// handle the route
$routeHandler->getRoute($routeLabel)->invoke();
