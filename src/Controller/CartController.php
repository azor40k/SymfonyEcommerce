<?php

namespace App\Controller;
use App\Repository\CartContentRepository;
use App\Repository\CartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/profile/order")
 */
class CartController extends AbstractController
{
    /**
     * @Route("/", name="cart")
     */
    public function index(CartRepository $cartRepository, CartContentRepository $cartContentRepository)
    {
        return $this->render('cart/index.html.twig', [
            'cartCurrent' =>  $cartCurrent = $cartRepository->findBy(['user' => $this->getUser(), 'state' => false]),
            'contentCurrent' =>  $cartContentRepository->findBy(['cart' => $cartCurrent]),
        ]);
    }
}
