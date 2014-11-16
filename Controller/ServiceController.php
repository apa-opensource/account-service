<?php

namespace FNC\Bundle\AccountServiceBundle\Controller;

use FNC\Bundle\AccountServiceBundle\Converter\ConverterChain;
use FNC\Bundle\AccountServiceBundle\Entity\Account;
use FNC\Bundle\AccountServiceBundle\Entity\History;

use FNC\Bundle\AccountServiceBundle \Service\Service;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ServiceController extends Controller
{
    /**
     * @var string
     */
    const ERR_SERVICE_NEGATIVE_AMOUNT = 1416101130;

    /**
     * Redeem Account.
     *
     * @param Request $request
     * @param Account $account
     *
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {
        $amount           = $request->get('amount', 0);
        $currency         = $request->get('currency');
        $type             = $request->get('type');
        $pin              = $request->get('pin');
        $number           = $request->get('number');
        $referenceCode    = $request->get('referenceCode', Account::REFERENCE_CODE_DEFAULT);
        $referenceMessage = $request->get('referenceMessage');
        $transactionCode  = $request->get('transactionCode');

        /* @var Service $service*/
        $service = $this->get('fnc_account.service');

        $account = $service->create($amount, $currency, $referenceCode, $referenceMessage, $transactionCode, $type, $pin, $number);

        return new JsonResponse(array(
            'number' => $account->getNumber(),
            'pin'    => $account->getPin()
        ));

    }

    /**
     * Redeem Account.
     *
     * @param Request $request
     * @param Account $account
     *
     * @return JsonResponse
     */
    public function updateAction(Request $request, Account $account)
    {
        $type             = $request->get('type');
        $pin              = $request->get('pin');
        $number           = $request->get('number');


        /* @var Service $service*/
        $service = $this->get('fnc_account.service');

        $account = $service->update($account, $type, $pin, $number);

        return new JsonResponse(array(
            'id'     => $account->getId(),
            'number' => $account->getNumber(),
            'pin'    => $account->getPin()
        ));

    }
    /**
     * Redeem Account.
     *
     * @param Request $request
     * @param Account $account
     *
     * @return JsonResponse
     *
     * @ParamConverter("account", class="FNC\AccountBundle\Entity\Account")
     */
    public function loadAction(Request $request, Account $account)
    {
        $amount           = $request->get('amount');
        $currency         = $request->get('currency');
        $referenceCode    = $request->get('referenceCode');
        $referenceMessage = $request->get('referenceMessage');
        $transactionCode  = $request->get('transactionCode');

        if($amount < 0) {
            throw new \Exception('Positive Amount required', self::ERR_SERVICE_NEGATIVE_AMOUNT);
        }

        /* @var Service $service*/
        $service = $this->get('fnc_account.service');

        $rest =
            $service->booking($account, $amount, $currency, $referenceCode, $referenceMessage, $transactionCode);

        return new JsonResponse(array(
            'balance' => $account->getBalance()));
    }

    /**
     * Redeem Account.
     *
     * @param Request $request
     * @param Account $account
     *
     * @return JsonResponse
     *
     * @ParamConverter("account", class="FNC\AccountBundle\Entity\Account")
     */
    public function redeemAction(Request $request, Account $account)
    {
        $amount           = $request->get('amount');
        $currency         = $request->get('currency');
        $referenceCode    = $request->get('referenceCode');
        $referenceMessage = $request->get('referenceMessage');
        $transactionCode  = $request->get('transactionCode');

        if($amount < 0) {
            throw new \Exception('Positive Amount required', self::ERR_SERVICE_NEGATIVE_AMOUNT);
        }

        /* @var Service $service*/
        $service = $this->get('fnc_account.service');

        $rest =
            $service->booking($account, -1 * $amount, $currency, $referenceCode, $referenceMessage, $transactionCode);

        return new JsonResponse(array(
            'rest' => $rest));
    }

    /**
     * Cancellation.
     *
     * @param Request $request
     * @param Account $account
     *
     * @return mixed
     *
     * @ParamConverter("account", class="FNC\AccountBundle\Entity\Account")
     */
    public function cancelAction(Account $account)
    {
        /* @var Service $service*/
        $service = $this->get('fnc_account.service');

        $service->cancel($account);

        return new JsonResponse(array('disabled' => $account->isDisabled()));
    }

    /**
     * Cancellation.
     *
     * @param Request $request
     * @param Account $account
     *
     * @return mixed
     *
     * @ParamConverter("account", class="FNC\AccountBundle\Entity\Account")
     */
    public function activateAction(Account $account)
    {
        /* @var Service $service*/
        $service = $this->get('fnc_account.service');

        $service->activate($account);

        return new JsonResponse(array('disabled' => $account->isDisabled()));
    }

    /**
     * @param Request $request
     * @param Account $account
     *
     * @return mixed
     *
     * @ParamConverter("account", class="FNC\AccountBundle\Entity\Account")
     */
    public function statusAction(Account $account)
    {
        return new JsonResponse(array('disabled' => $account->isDisabled()));
    }

    /**
     * @param $cardNumber
     * @param $pin
     * @return mixed
     *
     * @ParamConverter("account", class="FNC\AccountBundle\Entity\Account")
     */
    public function balanceAction(Account $account)
    {
        return new JsonResponse(array(
            'balance'   => $account->getBalance(),
            'currency'  => $account->getCurrency()));
    }

    /**
     * @param  Account      $account
     * @return JsonResponse
     * @ParamConverter("account", class="FNC\AccountBundle\Entity\Account")
     */
    public function infoAction(Account $account)
    {
        /* @var ConverterChain $converter */
        $converter = $this->get('fnc_account.converter_chain');

        return new JsonResponse($converter->convert($account));
    }

    /**
     * @param  Account      $account
     * @return JsonResponse
     * @ParamConverter("account", class="FNC\AccountBundle\Entity\Account")
     */
    public function historyAction(Account $account)
    {
        /* @var ConverterChain $converter */
        $converter = $this->get('fnc_account.converter_chain');

        $list = array();

        foreach ($account->getHistory() as $history) {
            $list[] = $converter->convert($history);
        }

        return new JsonResponse($list);
    }
}
