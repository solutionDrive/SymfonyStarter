<?php

declare(strict_types=1);

namespace spec\App\EventListener;

use App\Entity\Customer;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\User\Model\UserInterface;

final class DefaultUsernameORMListenerSpec extends ObjectBehavior
{
    function it_sets_usernames_on_customer_create(
        OnFlushEventArgs $onFlushEventArgs,
        EntityManager $entityManager,
        UnitOfWork $unitOfWork,
        Customer $customer,
        UserInterface $user,
        ClassMetadata $userMetadata
    ): void {
        $onFlushEventArgs->getEntityManager()->willReturn($entityManager);
        $entityManager->getUnitOfWork()->willReturn($unitOfWork);

        $unitOfWork->getScheduledEntityInsertions()->willReturn([$customer]);
        $unitOfWork->getScheduledEntityUpdates()->willReturn([]);

        $user->getUsername()->willReturn(null);
        $user->getUsernameCanonical()->willReturn(null);
        $customer->getUser()->willReturn($user);
        $customer->getEmail()->willReturn('customer+extra@email.com');
        $customer->getEmailCanonical()->willReturn('customer@email.com');

        $user->setUsername('customer+extra@email.com')->shouldBeCalled();
        $user->setUsernameCanonical('customer@email.com')->shouldBeCalled();

        $entityManager->getClassMetadata(get_class($user->getWrappedObject()))->willReturn($userMetadata);
        $unitOfWork->recomputeSingleEntityChangeSet($userMetadata, $user)->shouldBeCalled();

        $this->onFlush($onFlushEventArgs);
    }

    function it_updates_usernames_on_customer_email_change(
        OnFlushEventArgs $onFlushEventArgs,
        EntityManager $entityManager,
        UnitOfWork $unitOfWork,
        Customer $customer,
        UserInterface $user,
        ClassMetadata $userMetadata
    ): void {
        $onFlushEventArgs->getEntityManager()->willReturn($entityManager);
        $entityManager->getUnitOfWork()->willReturn($unitOfWork);

        $unitOfWork->getScheduledEntityInsertions()->willReturn([]);
        $unitOfWork->getScheduledEntityUpdates()->willReturn([$customer]);

        $user->getUsername()->willReturn('user+extra@email.com');
        $user->getUsernameCanonical()->willReturn('customer@email.com');
        $customer->getUser()->willReturn($user);
        $customer->getEmail()->willReturn('customer+extra@email.com');
        $customer->getEmailCanonical()->willReturn('customer@email.com');

        $user->setUsername('customer+extra@email.com')->shouldBeCalled();
        $user->setUsernameCanonical('customer@email.com')->shouldBeCalled();

        $entityManager->getClassMetadata(get_class($user->getWrappedObject()))->willReturn($userMetadata);
        $unitOfWork->recomputeSingleEntityChangeSet($userMetadata, $user)->shouldBeCalled();

        $this->onFlush($onFlushEventArgs);
    }

    function it_updates_usernames_on_customer_email_canonical_change(
        OnFlushEventArgs $onFlushEventArgs,
        EntityManager $entityManager,
        UnitOfWork $unitOfWork,
        Customer $customer,
        UserInterface $user,
        ClassMetadata $userMetadata
    ): void {
        $onFlushEventArgs->getEntityManager()->willReturn($entityManager);
        $entityManager->getUnitOfWork()->willReturn($unitOfWork);

        $unitOfWork->getScheduledEntityInsertions()->willReturn([]);
        $unitOfWork->getScheduledEntityUpdates()->willReturn([$customer]);

        $user->getUsername()->willReturn('customer+extra@email.com');
        $user->getUsernameCanonical()->willReturn('user@email.com');
        $customer->getUser()->willReturn($user);
        $customer->getEmail()->willReturn('customer+extra@email.com');
        $customer->getEmailCanonical()->willReturn('customer@email.com');

        $user->setUsername('customer+extra@email.com')->shouldBeCalled();
        $user->setUsernameCanonical('customer@email.com')->shouldBeCalled();

        $entityManager->getClassMetadata(get_class($user->getWrappedObject()))->willReturn($userMetadata);
        $unitOfWork->recomputeSingleEntityChangeSet($userMetadata, $user)->shouldBeCalled();

        $this->onFlush($onFlushEventArgs);
    }

    function it_does_not_update_usernames_when_customer_emails_are_the_same(
        OnFlushEventArgs $onFlushEventArgs,
        EntityManager $entityManager,
        UnitOfWork $unitOfWork,
        Customer $customer,
        UserInterface $user,
        ClassMetadata $userMetadata
    ): void {
        $onFlushEventArgs->getEntityManager()->willReturn($entityManager);
        $entityManager->getUnitOfWork()->willReturn($unitOfWork);

        $unitOfWork->getScheduledEntityInsertions()->willReturn([]);
        $unitOfWork->getScheduledEntityUpdates()->willReturn([$customer]);

        $user->getUsername()->willReturn('customer+extra@email.com');
        $user->getUsernameCanonical()->willReturn('customer@email.com');
        $customer->getUser()->willReturn($user);
        $customer->getEmail()->willReturn('customer+extra@email.com');
        $customer->getEmailCanonical()->willReturn('customer@email.com');

        $user->setUsername(Argument::any())->shouldNotBeCalled();
        $user->setUsernameCanonical(Argument::any())->shouldNotBeCalled();

        $unitOfWork->recomputeSingleEntityChangeSet(Argument::cetera())->shouldNotBeCalled();

        $this->onFlush($onFlushEventArgs);
    }

    function it_does_nothing_on_customer_create_when_no_user_associated(
        OnFlushEventArgs $onFlushEventArgs,
        EntityManager $entityManager,
        UnitOfWork $unitOfWork,
        Customer $customer
    ): void {
        $onFlushEventArgs->getEntityManager()->willReturn($entityManager);
        $entityManager->getUnitOfWork()->willReturn($unitOfWork);

        $unitOfWork->getScheduledEntityInsertions()->willReturn([$customer]);
        $unitOfWork->getScheduledEntityUpdates()->willReturn([]);

        $customer->getUser()->willReturn(null);

        $unitOfWork->recomputeSingleEntityChangeSet(Argument::cetera())->shouldNotBeCalled();

        $this->onFlush($onFlushEventArgs);
    }

    function it_does_nothing_on_customer_update_when_no_user_associated(
        OnFlushEventArgs $onFlushEventArgs,
        EntityManager $entityManager,
        UnitOfWork $unitOfWork,
        Customer $customer
    ): void {
        $onFlushEventArgs->getEntityManager()->willReturn($entityManager);
        $entityManager->getUnitOfWork()->willReturn($unitOfWork);

        $unitOfWork->getScheduledEntityInsertions()->willReturn([]);
        $unitOfWork->getScheduledEntityUpdates()->willReturn([$customer]);

        $customer->getUser()->willReturn(null);
        $customer->getEmail()->willReturn('customer@email.com');

        $unitOfWork->recomputeSingleEntityChangeSet(Argument::cetera())->shouldNotBeCalled();

        $this->onFlush($onFlushEventArgs);
    }

    function it_does_nothing_when_there_are_no_objects_scheduled_in_the_unit_of_work(
        OnFlushEventArgs $onFlushEventArgs,
        EntityManager $entityManager,
        UnitOfWork $unitOfWork
    ): void {
        $onFlushEventArgs->getEntityManager()->willReturn($entityManager);
        $entityManager->getUnitOfWork()->willReturn($unitOfWork);

        $unitOfWork->getScheduledEntityInsertions()->willReturn([]);
        $unitOfWork->getScheduledEntityUpdates()->willReturn([]);

        $unitOfWork->recomputeSingleEntityChangeSet(Argument::cetera())->shouldNotBeCalled();

        $this->onFlush($onFlushEventArgs);
    }

    function it_does_nothing_when_there_are_other_objects_than_customer(
        OnFlushEventArgs $onFlushEventArgs,
        EntityManager $entityManager,
        UnitOfWork $unitOfWork,
        \stdClass $stdObject,
        \stdClass $stdObject2
    ): void {
        $onFlushEventArgs->getEntityManager()->willReturn($entityManager);
        $entityManager->getUnitOfWork()->willReturn($unitOfWork);

        $unitOfWork->getScheduledEntityInsertions()->willReturn([$stdObject]);
        $unitOfWork->getScheduledEntityUpdates()->willReturn([$stdObject2]);

        $unitOfWork->recomputeSingleEntityChangeSet(Argument::cetera())->shouldNotBeCalled();

        $this->onFlush($onFlushEventArgs);
    }
}
