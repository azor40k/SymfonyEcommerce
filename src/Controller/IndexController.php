<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartContent;
use App\Entity\Product;
use App\Form\CartContentType;
use App\Repository\CartRepository;
use App\Repository\CartContentRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(ProductRepository $productRepository)
    {
        return $this->render('index/index.html.twig', [
            //affichage des produits sur le template
            'products' => $productRepository->findAll(),
        ]);
    }

    /**
     * @Route("/proshow/{nom}", name="proshow")
     */
    public function proshow(Request $request, ProductRepository $productRepository, Product $product=null, CartRepository $cartRepository, CartContentRepository $cartContentRepository, TranslatorInterface $translator)
    {   
        //vérification que le produit existe        
        if($product !=null) {

            //vérification qu'un utilisateur est bien connecté
            if($this->getUser() == null){
                $this->addFlash("danger", $translator->trans('file.connect'));
                return $this->redirectToRoute('login');
            }

            $em=$this->getDoctrine()->getManager();

            //vérification que l'user connecté possède un panier non acheté
            if($cartRepository->findOneBy(['user' => $this->getUser(), 'state' => false]) == false){
                //si false création d'un panier pour l'utilisateur connecté
                $cart=new Cart();
                $cart->setUser($this->getUser());
                $em->persist($cart);
                $em->flush();
            }
            else{
                $cart = $cartRepository->findOneBy(['user' => $this->getUser(), 'state' => false]);
            }            
            
            //création d'un nouvel élément            
            $cartContent = new CartContent();
            $form=$this->createForm(CartContentType::class, $cartContent);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid())
            {
                //vérification que le produit existe déjà dans le contenu du panier actuel
                $cartContentCheck = $cartContentRepository->findOneBy(['product' => $product, 'cart' => $cart]);
                if($cartContentCheck != null)
                {
                    //vérification que l'addition de l'ancienne et nouvelle valeurs n'est pas supérieur au stock
                    if($product->getStock() >= $cartContentCheck->getQuantity() + $form->get('quantity')->getData()) {

                        $cartContent->setDateAdd(new \DateTime());
                        $cartContent->setQuantity( $cartContentCheck->getQuantity() + $form->get('quantity')->getData() );
                        $cartContent->setProduct($cartContentCheck->getProduct());
                        $cartContent->setCart($cartContentCheck->getCart());
                        
                        //supression de l'ancienne valeur du tableau
                        $em->remove($cartContentCheck);
                        //ajout de la nouvelle valeur dans le tableau
                        $em->persist($cartContent);
                        $em->flush();

                        $this->addFlash("success", $translator->trans('file.proadd') );
                        return $this->redirectToRoute('cart_content');
                    }
                    else{
                        //message quantité trop importante
                        $this->addFlash("danger", $translator->trans('file.error'));
                        return $this->redirectToRoute('index');
                    }
                }
                else{
                    //vérification que la valeur entré n'est pas inférieur à 0 ou supérieur au stock du produit
                    if($product->getStock() >=$cartContent->getQuantity() && $cartContent->getQuantity() > 0) {

                        $cartContent->setDateAdd(new \DateTime());
                        $cartContent->setProduct($product);
                        $cartContent->setCart($cart);

                        //ajout du nouveau produit dans le contenu du panier
                        $em->persist($cartContent);
                        $em->flush();

                        $this->addFlash("success", $translator->trans('file.proadd'));
                        return $this->redirectToRoute('cart_content');
                    }
                    else{
                        //message quantité invalide
                        $this->addFlash("danger", $translator->trans('file.prono'));
                        return $this->redirectToRoute('proshow', ['id' => $product->getId() ]);
                    }
                }    
            }
            return $this->render('index/proshow.html.twig', [
                'product' => $productRepository->findOneBy(['id' => $product]),
                'form_cart_add'=> $form->createView(),
            ]);
        }        
        else{
            $this->addFlash("danger", $translator->trans('file.pro404'));
            return $this->redirectToRoute('index');
        }            
    }        
}