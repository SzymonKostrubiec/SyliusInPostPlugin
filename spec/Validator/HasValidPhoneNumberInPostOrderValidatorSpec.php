<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
 */

declare(strict_types=1);

namespace spec\BitBag\SyliusInPostPlugin\Validator;

use BitBag\SyliusInPostPlugin\Checker\ShippingMethodCheckerInterface;
use BitBag\SyliusInPostPlugin\Model\InPostPointsAwareInterface;
use BitBag\SyliusInPostPlugin\Validator\Constraint\HasValidPhoneNumberInPostOrder;
use BitBag\SyliusInPostPlugin\Validator\HasValidPhoneNumberInPostOrderValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use stdClass;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;
use Tests\BitBag\SyliusInPostPlugin\Spec\Builder\AddressBuilder;

final class HasValidPhoneNumberInPostOrderValidatorSpec extends ObjectBehavior
{
    public function let(ShippingMethodCheckerInterface $shippingMethodChecker): void
    {
        $this->beConstructedWith($shippingMethodChecker);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(HasValidPhoneNumberInPostOrderValidator::class);
    }

    public function it_should_throw_exception_if_value_is_not_instance_of_order_interface(
        ExecutionContextInterface $context,
        Constraint $constraint
    ): void {
        $context->buildViolation(Argument::type('string'))->shouldNotBeCalled();
        $value = new stdClass();

        $this->initialize($context);
        $this->shouldThrow(new \Exception('Value must be instance of OrderInterface'))
        ->during('validate', [$value, $constraint]);
    }

    public function it_should_throw_exception_if_value_is_not_implementing_inpost_points_aware_interface(
        ExecutionContextInterface $context,
        Constraint $constraint,
        OrderInterface $value
    ): void {
        $context->buildViolation(Argument::type('string'))->shouldNotBeCalled();

        $this->initialize($context);
        $this->shouldThrow(new \Exception('Value must be instance of InPostPointsAwareInterface'))
            ->during('validate', [$value, $constraint]);
    }

    public function it_should_do_nothing_if_selected_shipping_method_is_not_inpost(
        ExecutionContextInterface $context,
        Constraint $constraint,
        OrderInterface $value,
        ShippingMethodCheckerInterface $shippingMethodChecker
    ): void {
        $value->implement(InPostPointsAwareInterface::class);
        $context->buildViolation(Argument::type('string'))->shouldNotBeCalled();
        $shippingMethodChecker->isInPost(Argument::type(OrderInterface::class))->willReturn(false);

        $this->initialize($context);
        $this->validate($value, $constraint);
    }

    public function it_should_do_nothing_if_phone_number_is_not_set(
        ExecutionContextInterface $context,
        Constraint $constraint,
        OrderInterface $value,
        ShippingMethodCheckerInterface $shippingMethodChecker
    ): void {
        $value->implement(InPostPointsAwareInterface::class);
        $value->getShippingAddress()->willReturn(null);
        $shippingMethodChecker->isInPost(Argument::type(OrderInterface::class))->willReturn(false);

        $context->buildViolation(Argument::type('string'))->shouldNotBeCalled();

        $this->initialize($context);
        $this->validate($value, $constraint);
    }

    public function it_should_add_violation_if_phone_number_is_too_short(
        ExecutionContextInterface $context,
        Constraint $constraint,
        OrderInterface $value,
        ConstraintViolationBuilderInterface $violationBuilder,
        ShippingMethodCheckerInterface $shippingMethodChecker,
        ): void {
        $addressBuilder = AddressBuilder::create()->withPhoneNumber('123')->build();
        $value->implement(InPostPointsAwareInterface::class);
        $value->getShippingAddress()->willReturn($addressBuilder);
        $shippingMethodChecker->isInPost(Argument::type(OrderInterface::class))->willReturn(true);

        $context->buildViolation(HasValidPhoneNumberInPostOrder::PHONE_NUMBER_IS_TOO_SHORT_MESSAGE)
            ->willReturn($violationBuilder);
        $violationBuilder->setParameter('{{ limit }}', (string) HasValidPhoneNumberInPostOrder::POLISH_PHONE_NUMBER_DEFAULT_LENGTH)
            ->willReturn($violationBuilder);
        $violationBuilder->atPath('shippingAddress.phoneNumber')
            ->willReturn($violationBuilder);

        $violationBuilder->addViolation()->shouldBeCalled();

        $this->initialize($context);
        $this->validate($value, $constraint);
    }

    public function it_should_add_violation_if_phone_number_is_too_long(
        ExecutionContextInterface $context,
        Constraint $constraint,
        OrderInterface $value,
        ConstraintViolationBuilderInterface $violationBuilder,
        ShippingMethodCheckerInterface $shippingMethodChecker,
        ): void {
        $addressBuilder = AddressBuilder::create()->withPhoneNumber('1234567890')->build();
        $value->implement(InPostPointsAwareInterface::class);
        $value->getShippingAddress()->willReturn($addressBuilder);
        $shippingMethodChecker->isInPost(Argument::type(OrderInterface::class))->willReturn(true);

        $context->buildViolation(HasValidPhoneNumberInPostOrder::PHONE_NUMBER_IS_TOO_LONG_MESSAGE)
            ->willReturn($violationBuilder);
        $violationBuilder->setParameter('{{ limit }}', (string) HasValidPhoneNumberInPostOrder::POLISH_PHONE_NUMBER_DEFAULT_LENGTH)
            ->willReturn($violationBuilder);
        $violationBuilder->atPath('shippingAddress.phoneNumber')
            ->willReturn($violationBuilder);

        $violationBuilder->addViolation()->shouldBeCalled();

        $this->initialize($context);
        $this->validate($value, $constraint);
    }

    public function it_should_do_nothing_if_phone_number_is_valid(
        ExecutionContextInterface $context,
        Constraint $constraint,
        OrderInterface $value,
        ConstraintViolationBuilderInterface $violationBuilder,
        ShippingMethodCheckerInterface $shippingMethodChecker,
        ): void {
        $addressBuilder = AddressBuilder::create()->withPhoneNumber('123456789')->build();
        $value->implement(InPostPointsAwareInterface::class);
        $value->getShippingAddress()->willReturn($addressBuilder);
        $shippingMethodChecker->isInPost(Argument::type(OrderInterface::class))->willReturn(true);

        $context->buildViolation(Argument::type('string'))->shouldNotBeCalled();

        $this->initialize($context);
        $this->validate($value, $constraint);
    }
}