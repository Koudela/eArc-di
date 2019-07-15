<?php

require __DIR__ . '/../vendor/autoload.php';

use eArc\DI\DependencyResolver;
use eArc\DI\Exceptions\ClassNotFoundException;
use eArc\DI\Exceptions\ExecuteCallableException;
use eArc\DI\Exceptions\MakeClassException;
use eArc\DI\ParameterBag;

class A
{
    protected $p;

    public function __construct()
    {
        $this->p = ParameterBag::get('p1');
    }

    public function sayHello()
    {
        echo "hello, I am A\n";
    }

    public function myParameter()
    {
        echo "my Parameter is {$this->p}!\n";
    }
}

class B extends A
{
    protected $a;

    /**
     * @throws ClassNotFoundException
     * @throws ExecuteCallableException
     * @throws MakeClassException
     */
    public function __construct()
    {
        parent::__construct();

        $this->a = DependencyResolver::get(A::class);
        $this->p = ParameterBag::get('p2');
    }

    public function getA()
    {
        return $this->a;
    }

    public function sayHello()
    {
        echo "hello, I am B\n";
    }
}

class C
{
    protected $a;
    protected $b;

    /**
     * @throws ClassNotFoundException
     * @throws ExecuteCallableException
     * @throws MakeClassException
     */
    public function __construct()
    {
        $this->a = DependencyResolver::get(A::class);
        $this->b = DependencyResolver::get(B::class);
    }

    public function getA()
    {
        return $this->a;
    }

    public function getB()
    {
        return $this->b;
    }
}

class D extends A
{
    public function sayHello() {
        echo "I decorate A\n";
    }
}

ParameterBag::import(['p1' => 'Hase', 'p2' => 'Igel']);
/* @var C $c */
$c = DependencyResolver::get(C::class);
$c->getB()->getA()->sayHello();
$c->getA()->sayHello();
$c->getB()->sayHello();
$c->getA()->myParameter();
DependencyResolver::decorate(A::class, D::class);
$c = DependencyResolver::make(C::class);
$c->getB()->getA()->sayHello();
$c->getA()->sayHello();
$c->getB()->sayHello();
$c->getB()->myParameter();
DependencyResolver::clearCache(B::class);
$c = DependencyResolver::make(C::class);
$c->getB()->getA()->sayHello();
$c->getA()->sayHello();
$c->getB()->sayHello();
