<?php

namespace App\Controller;
use App\Repository\CartContentRepository;
use App\Repository\CartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    /**
     * @Route("/profile/order", name="cart")
     */
    public function index(CartRepository $cartRepository, CartContentRepository $cartContentRepository)
    {   
        //return et recherche des anciens paniers et contenu
        return $this->render('cart/index.html.twig', [
            'cartOld' =>  $cartOld = $cartRepository->findTrueByNewest(['user' => $this->getUser()]),
            'contentOld' =>  $cartContentRepository->findBy(['cart' => $cartOld]),
        ]);
    }

    /**
     * @Route("/superadmin/ongoingOrder", name="ongoingOrder")
     */
    public function ongoingOrder(CartRepository $cartRepository, CartContentRepository $cartContentRepository)
    {
        //return et affichage des paniers en cours d'achat
        return $this->render('admin/super_admin/order.html.twig', [
            'cartFalse' =>  $cartFalse = $cartRepository->findFalseByNewest(),
            'contentFalse' =>  $cartContentRepository->findBy(['cart' => $cartFalse]),
        ]);
    }
}
