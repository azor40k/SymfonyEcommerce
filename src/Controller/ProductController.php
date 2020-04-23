<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 *  //sécurité vérification admin
 * @Route("/admin")
 */
class ProductController extends AbstractController
{
    /**
     * @Route("/", name="admin", methods={"GET"})
     */
    public function index(ProductRepository $productRepository): Response
    {
        //affiche de tous les produits
        return $this->render('admin/product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="product_new", methods={"GET","POST"})
     */
    public function new(Request $request, TranslatorInterface $translator): Response
    {
        //création dun nouveau produit via les infos du formulaire
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //récupération de l'image
            $photo=$form->get('picture')->getData();

            //vérification d'une image présente pour le produit
            if($photo == null){
                $this->addFlash("danger", $translator->trans('file.pro404p'));
                    return $this->redirectToRoute('product_new');
            }

            //upload photo
            if($photo) {
                $nomPhoto=uniqid() . '.'. $photo->guessExtension();

                try {
                    $photo->move($this->getParameter('upload_product'),
                        $nomPhoto);
                }

                catch(FileException $e) {
                    $this->addFlash("danger", 'Error');
                    return $this->redirectToRoute('admin');
                }

                $product->setPicture($nomPhoto);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash("success", $translator->trans('file.procreate'));
            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/product/{id}", name="product_show", methods={"GET"})
     */
    public function show(Product $product): Response
    {
        //affichage du produit
        return $this->render('admin/product/show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * @Route("/product/{id}/edit", name="product_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Product $product, TranslatorInterface $translator): Response
    {
        //création du formulaire et récupération de donnée
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentPicture = $product->getPicture();
            $photo = $form->get('picture')->getData();
            
            //vérification si on change la photo à la MAJ du produit
            if($photo && $currentPicture != $photo){
                unlink($this->getParameter('upload_product').$product->getPicture());

                $nomPhoto=uniqid() . '.'. $photo->guessExtension();

                try {
                    $photo->move($this->getParameter('upload_product'),
                        $nomPhoto);
                }

                catch(FileException $e) {
                    $this->addFlash("danger", $translator->trans('file.error'));
                    return $this->redirectToRoute('admin');
                }
                $product->setPicture($nomPhoto);
            }
            $this->getDoctrine()->getManager()->flush();

            //message Update success
            $this->addFlash("success", $translator->trans('file.promaj'));
            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/product/{id}/delete", name="product_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Product $product, TranslatorInterface $translator): Response
    {   
        //vérification de validation
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {

            //sécurité vérification admin
            if($this->getUser()->hasRole('ROLE_ADMIN') == false )
            {
                $this->addFlash("danger", $translator->trans('file.permission'));
                return $this->redirectToRoute('profile');
            }
            
            //supression de l'image dans le dossier upload
            unlink($this->getParameter('upload_product').$product->getPicture());            

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($product);
            $entityManager->flush();

            $this->addFlash("success", $translator->trans('file.prodel'));
        }

        return $this->redirectToRoute('admin');
    }
}
