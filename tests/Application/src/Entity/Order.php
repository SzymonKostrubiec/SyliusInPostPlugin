<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace Tests\BitBag\SyliusInPostPlugin\Application\src\Entity;

use Sylius\Component\Core\Model\Order as BaseOrder;
use BitBag\SyliusInPostPlugin\Model\OrderPointTrait;

class Order extends BaseOrder
{
    use OrderPointTrait;
}
