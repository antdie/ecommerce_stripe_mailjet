<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Service\MailjetManager;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Workflow\WorkflowInterface;

class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $toAssigned = Action::new('toAssigned', 'Ready to deliver', 'fas fa-angle-double-up')->linkToCrudAction('toAssigned');
        $toDelivered = Action::new('toDelivered', 'Delivered', 'fas fa-angle-double-down')->linkToCrudAction('toDelivered');

        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE)
            ->add('detail', $toDelivered)
            ->add('detail', $toAssigned)
            ->add('index', 'detail');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            DateTimeField::new('createdAt')->hideOnForm(),
            AssociationField::new('customer')->onlyOnDetail(),
            TextField::new('carrierName')->onlyOnDetail(),
            TextField::new('address')->onlyOnDetail(),
            MoneyField::new('total')->setCurrency('EUR')->hideOnForm(),
            ChoiceField::new('state')->setChoices([
                'Pending' => 'pending',
                'Paid' => 'paid',
                'Assigned' => 'assigned',
                'Delivered' => 'delivered',
            ])->renderExpanded(),
            ArrayField::new('orderDetails')->onlyOnDetail()
        ];
    }

    public function toAssigned(MailjetManager $mailjetManager, AdminContext $context, WorkflowInterface $orderStateMachine, EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator)
    {
        $order = $context->getEntity()->getInstance();
        $customer = $order->getCustomer();

        if ($orderStateMachine->can($order, 'to_assigned')) {
            $orderStateMachine->apply($order, 'to_assigned');
            $entityManager->flush();

            $mailjetManager->send($customer->getEmail(), (string) $customer, 'Successfully assigned to '.$order->getCarrierName(), 'Lorem ipsum dolor fs lghke a sqd nfgsk qdqs q fdqs sd.');

            $this->addFlash('success', 'Order '.$order->getId().' updated to assigned.');
        } else {
            $this->addFlash('danger', 'Order '.$order->getId().' can\'t be updated to assigned (the order is "'.$order->getState().'", and need to be in "paid" state).');
        }

        $url = $adminUrlGenerator
            ->setController(OrderCrudController::class)
            ->setAction('index');

        return $this->redirect($url);
    }

    public function toDelivered(MailjetManager $mailjetManager, AdminContext $context, WorkflowInterface $orderStateMachine, EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator)
    {
        $order = $context->getEntity()->getInstance();
        $customer = $order->getCustomer();

        if ($orderStateMachine->can($order, 'to_delivered')) {
            $orderStateMachine->apply($order, 'to_delivered');
            $entityManager->flush();

            $mailjetManager->send($customer->getEmail(), (string) $customer, 'Successfully delivered', 'Lorem ipsum dolor fs lghke a sqd nfgsk qdqs q fdqs sd.');

            $this->addFlash('success', 'Order '.$order->getId().' updated to delivered.');
        } else {
            $this->addFlash('danger', 'Order '.$order->getId().' can\'t be updated to delivered (the order is "'.$order->getState().'", and need to be in "assigned" state).');
        }

        $url = $adminUrlGenerator
            ->setController(OrderCrudController::class)
            ->setAction('index');

        return $this->redirect($url);
    }
}
