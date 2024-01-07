<?php

namespace App\Controller;

use App\Entity\Deputy;
use App\Entity\Order;
use App\Form\ConfirmationForm;
use App\Form\DeputyForm;
use App\Service\DeputyService;
use App\Service\OrderService;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeputyController extends AbstractController
{
    private EntityManager $em;

    private DeputyService $deputyService;

    private OrderService $orderService;

    /**
     * DeputyController constructor.
     */
    public function __construct(EntityManager $em, DeputyService $deputyService, OrderService $orderService)
    {
        $this->em = $em;
        $this->deputyService = $deputyService;
        $this->orderService = $orderService;
    }

    /**
     * @Route("/order/{orderId}/deputy/add", name="deputy-add")
     */
    public function add(Request $request, int $orderId): RedirectResponse|Response
    {
        $order = $this->orderService->getOrderByIdIfNotServed($orderId);

        $deputy = new Deputy($order);

        $deputyType = !empty($request->get('deputyType')) ? $request->get('deputyType') : null;

        $form = $this->createForm(DeputyForm::class, $deputy, ['deputyType' => $deputyType]);
        $form->handleRequest($request);

        $buttonClicked = $form->getClickedButton();

        if ($form->isSubmitted() && $form->isValid()) {
            $order->addDeputy($deputy);
            $this->em->persist($deputy);
            $this->em->flush();

            if ('saveAndAddAnother' == $buttonClicked->getName()) {
                return $this->redirectToRoute('deputy-add', ['orderId' => $order->getId()]);
            }

            return $this->redirectToRoute('order-summary', ['orderId' => $order->getId()]);
        }

        return $this->render('Deputy/add.html.twig', [
            'client' => $order->getClient(),
            'order' => $order,
            'form' => $form->createView(),
            'deputyType' => $deputyType
        ]);
    }

    /**
     * @Route("/order/{orderId}/deputy/add/deputy-type", name="deputy-type")
     */
    public function chooseDeputyType(Request $request, int $orderId): Response
    {
        $order = $this->orderService->getOrderByIdIfNotServed($orderId);

        return $this->render('Deputy/type.html.twig', [
            'order' => $order,
        ]);
    }

    /**
     * @Route("/case/order/{orderId}/deputy/edit/{deputyId}", name="deputy-edit")
     */
    public function edit(Request $request, $orderId, $deputyId): RedirectResponse|Response
    {
        $order = $this->em->getRepository(Order::class)->find($orderId);

        $deputy = $order->getDeputyById($deputyId);

        if (!$deputy instanceof Deputy) {
            throw new \RuntimeException('Unknown Deputy');
        }

        $form = $this->createForm(DeputyForm::class, $deputy, ['deputyType' => $deputy->getDeputyType()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($deputy);
            $this->em->flush();

            return $this->redirectToRoute('order-summary', ['orderId' => $order->getId()]);
        }

        return $this->render('Deputy/add.html.twig', [
            'client' => $order->getClient(),
            'order' => $order,
            'form' => $form->createView(),
            'deputyType' => $deputy->getDeputyType()
        ]);
    }

    /**
     * @Route("/case/order/{orderId}/deputy/delete/{deputyId}", name="deputy-delete")
     */
    public function delete(Request $request, $orderId, $deputyId): RedirectResponse|Response
    {
        $order = $this->em->getRepository(Order::class)->find($orderId);

        $deputy = $order->getDeputyById($deputyId);

        if (!$deputy instanceof Deputy) {
            throw new \RuntimeException('Unknown Deputy');
        }

        $form = $this->createForm(ConfirmationForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->em->remove($deputy);
            $this->em->flush();

            return $this->redirectToRoute('order-summary', ['orderId' => $order->getId()]);
        }

        return $this->render('Common/confirm.html.twig', [
            'client' => $order->getClient(),
            'order' => $order,
            'form' => $form->createView()
        ]);
    }
}
