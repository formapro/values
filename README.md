[![Build Status](https://travis-ci.org/makasim/values.png?branch=master)](https://travis-ci.org/makasim/values)

# Values

**DEPRECATED**: Use [https://github.com/makasim/yadm](https://github.com/makasim/yadm).

This approach tries to gather the best from arrays and objects.
You can store anything into an array, the array then could be easily converted to json, or saved in mongo.
Objects can describe intention by their methods (getter or setters).
When using arrays you have to always make sure keys are present in array (by doing isset), with objects you can rely on their methods.

Create a model class:

```php
<?php

class Order
{
    use \Makasim\Values\ValuesTrait;

    public function getNumber()
    {
        return $this->getValue('self', 'number');
    }

    public function setNumber($number)
    {
        $this->setValue('self', 'number', $number);
    }
}
```

Create a new model:

```php
<?php

// create new order
$order = new Order;

$order->setNumber('1234');
$number = $order->getNumber();

// get an order representation as an array. now you can store it (to mongo for example).
$orderValues = \Makasim\Values\get_values($order);
```

Hydrate a model from an array:

```php
<?php

$orderValues = [/* an array previously stored somewhere*/];

// create new order
$order = new Order;
\Makasim\Values\set_values($order, $orderValues);

$number = $order->getNumber();
```

Set custom values:

```php
<?php

$order = new Order;

$order->setValue('subscription', 'id', 123);
$order->setValue('subscription', 'deliveryDate', '2015-10-10');
$order->setValue('fortnox', 'invoiceNumber', 543);
```

# Objects

Is a thin wrapper above values traits, which allows to build models tree, while still storing everything in the root.
For example we have an order and price where the order is the root and price is a tree leaf.

```php
<?php

class Order
{
    use \Makasim\Values\ValuesTrait;
    use \Makasim\Values\ObjectsTrait;

    public function getPrice()
    {
        return $this->getObject('self', 'price', Price::class);
    }

    public function setPrice(Price $price = null)
    {
        $this->setObject('self', 'price', $price);
    }
}

class Price
{
    use \Makasim\Values\ValuesTrait;

    public function getAmount()
    {
        return $this->getValue('self', 'amount', null, 'int');
    }

    public function setAmount($amount)
    {
        $this->setValue('self', 'amount', $amount);
    }
}
```

and usage example:

```php
<?php

$price = new Price();
$price->setAmount(100);

$order = new Order();
$order->setPrice($price);


// it contains all order values INCLUDING leaf models, in our case price ones.
$orderValues = \Makasim\Values\get_values($order);
```

if you update values of leaf model they are update in order too:

```php
<?php

$price = new Price();
$price->setAmount(100);

$order = new Order();
$order->setPrice($price);

$price->setAmount(200);

// the values must contain price 200
$orderValues = \Makasim\Values\get_values($order);
```

and you can easily hydrate your model from array:

```php
<?php

$order = new Order();
\Makasim\Values\set_values($order, $orderValues);

$price = $order->getPrice();

// if order values contains price, you will get a price instance
```
