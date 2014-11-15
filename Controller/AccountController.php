<?php

namespace FNC\AccountBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use FNC\AccountBundle\Entity\History;
use FNC\AccountBundle\Service\Service;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use FNC\AccountBundle\Entity\Account;
use FNC\AccountBundle\Form\AccountType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Account controller.
 *
 */
class AccountController extends Controller
{
    /**
     *
     */
    const BOOKING_DIRECTION_LOAD = 1;

    /**
     *
     */
    const BOOKING_DIRECTION_REDEEM = 2;

    /**
     * Lists all Account entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $dql   = "SELECT a FROM FNCAccountBundle:Account a";

        $query = $em->createQuery($dql);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1)/*page number*/,
            10/*limit per page*/
        );

        return $this->render('FNCAccountBundle:Account:index.html.twig', array(
            'pagination' => $pagination,
        ));
    }
    /**
     * Creates a new Account entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Account();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('management_account_show', array('id' => $entity->getId())));
        }

        return $this->render('FNCAccountBundle:Account:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Account entity.
     *
     * @param Account $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Account $entity)
    {
        $types = $this->container->getParameter('fnc_account.types');
        $currencies = $this->container->getParameter('fnc_account.currencies');

        $form = $this->createForm(new AccountType(), $entity, array(
            'action'        => $this->generateUrl('management_account_create'),
            'method'        => 'POST',
            'types'         => array_combine($types, $types),
            'currencies'    => array_combine($currencies, $currencies)
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Account entity.
     *
     */
    public function newAction()
    {
        $entity = new Account();
        $form   = $this->createCreateForm($entity);

        return $this->render('FNCAccountBundle:Account:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Account entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FNCAccountBundle:Account')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Account entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FNCAccountBundle:Account:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Account entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FNCAccountBundle:Account')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Account entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('FNCAccountBundle:Account:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Account entity.
    *
    * @param Account $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Account $entity)
    {
        $types = $this->container->getParameter('fnc_account.types');
        $currencies = $this->container->getParameter('fnc_account.currencies');

        $form = $this->createForm(new AccountType(), $entity, array(
            'action'        => $this->generateUrl('management_account_update', array('id' => $entity->getId())),
            'method'        => 'PUT',
            'types'         => array_combine($types, $types),
            'currencies'    => array_combine($currencies, $currencies)
        ));

        $form->remove('currency');
        $form->remove('balance');

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Account entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        /* @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('FNCAccountBundle:Account')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Account entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {

            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('management_account_show', array('id' => $id)));
        }

        return $this->render('FNCAccountBundle:Account:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a Account entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('FNCAccountBundle:Account')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Account entity.');
            }

            /* @var History $history*/
            foreach ($entity->getHistory() as $history) {
                $em->remove($history);
            }

            $em->remove($entity);

            $em->flush();
        }

        return $this->redirect($this->generateUrl('management_account'));
    }

    /**
     * Creates a form to delete a Account entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('management_account_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm();
    }

    /**
     * @Template()
     */
    public function bookingAction(Request $request, $id)
    {
        /* @var Service $service */
        $service = $this->get('fnc_account.service');

        /* @var Translator $tr */
        $tr = $this->get('translator');

        /* @var EntityManager $em*/
        $em = $this->getDoctrine()->getManager();

        /* @var EntityRepository $repo */
        $repo = $em->getRepository('FNCAccountBundle:Account');

        /* @var Account $account */
        $account = $repo->find($id);

        $bookingForm = $this->createBookingForm($id);

        $bookingForm->handleRequest($request);

        $direction = $bookingForm->get('direction')->getData();

        $amount = ($direction === self::BOOKING_DIRECTION_LOAD ? 1 : -1 )* $bookingForm->get('amount')->getData();

        if ($request->isMethod('POST')) {
            try {
                $service->booking($account, $amount, $account->getCurrency(), Account::REFERENCE_CODE_BACKEND, microtime(), microtime());

                $message = $tr->trans('account.message.booking.success', array(
                    '%balance%' => number_format($account->getBalance()/100, 2, '.', ''),
                    '%currency%'=> $account->getCurrency()
                ));

                $this->get('braincrafted_bootstrap.flash')->success($message);
            } catch (\Exception $ex) {
                $message = $tr->trans('account.message.booking.error', array(
                    '%message%' => $ex->getMessage()
                ));

                $this->get('braincrafted_bootstrap.flash')->error($message);
            }
        }

        return array(
            'booking_form'  => $bookingForm->createView()
        );
    }

    /**
     * Creates a form to delete a Account entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createBookingForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('management_account_booking', array('id' => $id)))
            ->setMethod('POST')
            ->add('direction', 'choice', array(
                'choices'   => array(
                    self::BOOKING_DIRECTION_LOAD   => 'account.booking.form.direction.load',
                    self::BOOKING_DIRECTION_REDEEM => 'account.booking.form.direction.redeem'
                )
            ))
            ->add('amount')
            ->add('submit', 'submit', array('label' => 'account.booking.form.submit'))
            ->getForm();
    }
}
