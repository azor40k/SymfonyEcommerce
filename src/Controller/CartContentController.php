<?php

namespace App\Controller;

use App\Entity\CartContent;
use App\Repository\CartContentRepository;
use App\Repository\CartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
* @Route("/yourCart")
*/
class CartContentController extends AbstractController
{
    /**
     * @Route("/", name="cart_content")
     */
    public function index(CartRepository $cartRepository, CartContentRepository $cartContentRepository,TranslatorInterface $translator)
    {
        if($this->getUser() == null)
        {
            $this->addFlash("danger",$translator->trans('file.connect'));
            return $this->redirectToRoute('app_login');
        }

        return $this->render('cart_content/index.html.twig', [
            'cart' =>  $cart = $cartRepository->findOneBy(['user' => $this->getUser(), 'state' => false]),
            'contents' =>  $cartContentRepository->findBy(['cart' => $cart]),
        ]);
    }

    /**
    * @Route("/removeCart/{id}", name="removeCart")
    */
    public function removeCart(CartContent $cartContent=null, TranslatorInterface $translator)
    {   
        //vérification qu'il y a bien un produit enlever
        if($cartContent != null){
            
            //supression
            $em=$this->getDoctrine()->getManager();
            $em->remove($cartContent);
            $em->flush();

            // message produit enlever et redirection
            $this->addFlash("success", $translator->trans('file.prore'));
            return $this->redirectToRoute('cart_content');

        }
        else{
            //message erreur et redirection
            $this->addFlash("danger", $translator->trans('file.error'));
            return $this->redirectToRoute('cart_content');
        }

    }

    /**
     * @Route("/buyCart" , name="buyCart")
     */
    public function buyCart(CartRepository $cartRepository, CartContentRepository $cartContentRepository, TranslatorInterface $translator)
    {
        //récupération id du panier et du contenu
        $cart = $cartRepository->findOneBy(['user' => $this->getUser(), 'state' => false]);
        $cartContent =  $cartContentRepository->findBy(['cart' => $cart]);

        foreach($cartContent as $content)
        {
            $em=$this->getDoctrine()->getManager();

            //vérification si le produit à le stock nécessaire
            $x = $content->getQuantity();
            $quantiteCheck = $content->getProduct()->getStock() - $x;

            if($quantiteCheck < 0)
            {
                $this->addFlash("danger", $translator->trans('file.proout') . $content->getProduct()->getNom());
                return $this->redirectToRoute('cart_content');
            }

            $content->getProduct()->setStock($quantiteCheck);

            $em->persist($content);
            $em->flush();
        }

        $cart->setDateBought(new \DateTime());
        $cart->setState(true);

        $em->persist($cart);
        $em->flush();
        
        $this->addFlash("success", $translator->trans('file.cartook'));
        return $this->redirectToRoute('index');
    }
}



