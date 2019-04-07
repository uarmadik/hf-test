<?php

namespace App\Controller\Rest;

use App\Entity\Credit;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;


class IndexController extends AbstractFOSRestController
{
    /**
     * @Rest\Get("/credits")
     * @QueryParam(name="limit", requirements="\d+", default=100)
     * @QueryParam(name="offset", requirements="\d+", default=0)
     * @QueryParam(name="order-by", default="id")
     * @QueryParam(name="order-direction", default="asc")
     * @QueryParam(name="sum", requirements="\d+", default=null)
     * @QueryParam(name="period", requirements="\d+", default=null)
     */
    public function getCredits(ParamFetcher $paramFetcher)
    {
        try {

            $limit = $paramFetcher->get('limit');
            $offset = $paramFetcher->get('offset');
            $orderBy = $paramFetcher->get('order-by');
            $orderDirection = $paramFetcher->get('order-direction');
            $sum = (int) $paramFetcher->get('sum');
            $period = (int) $paramFetcher->get('period');

            $repository = $this->getDoctrine()->getRepository(Credit::class);

            $order = [$orderBy => $orderDirection];
            $credits = $repository->findBy([], $order, $limit, $offset);

            if ($sum !== 0 && $period !== 0) {
                foreach ($credits as $credit) {
                    $credit->setOverpay($sum, $period);
                }
            }

            $data = [
                'items' => $credits,
                'total' => count($credits),
            ];

            return View::create($data, Response::HTTP_OK);

        } catch (\Exception $e) {
            return View::create('Error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}