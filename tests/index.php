<?php

require __DIR__ . '/../vendor/autoload.php';

use eArc\DI\DI;

class A
{
    protected $p;

    public function __construct()
    {
        $this->p = di_param('p1');
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

    public function __construct()
    {
        parent::__construct();

        $this->a = di_get(A::class);
        $this->p = di_param('p2.px');
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

class C implements I1
{
    protected $a;
    protected $b;

    public function __construct()
    {
        $this->a = di_get(A::class);
        $this->b = di_get(B::class);
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
interface I1 {}
class D extends A
{
    public function sayHello() {
        echo "I decorate A\n";
    }
}

DI::init();
di_import_param(['p1' => 'Hase', 'p2' => ['px' => 'Igel']]);
/* @var C $c */
#di_decorate(I1::class, C::class);
$c = di_get(I1::class, C::class);
$c->getB()->getA()->sayHello();
$c->getA()->sayHello();
$c->getB()->sayHello();
$c->getA()->myParameter();
di_decorate(A::class, D::class);
$c = di_make(C::class);
$c->getB()->getA()->sayHello();
$c->getA()->sayHello();
$c->getB()->sayHello();
$c->getB()->myParameter();
di_clear_cache(B::class);
$c = di_make(C::class);
$c->getB()->getA()->sayHello();
$c->getA()->sayHello();
$c->getB()->sayHello();
