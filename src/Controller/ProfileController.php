<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileEditType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
* @Route("/profile")
*/
class ProfileController extends AbstractController
{
    /**
     * @Route("/", name="profile")
     */
    public function index()
    {
        return $this->render('profile/index.html.twig');
    }

    /**
     * @Route("/editProfile/{id}", name="editProfile")
     */
    public function editProfile(Request $request, User $user=null, TranslatorInterface $translator)
    {
        //vérification si user existant
        if( $user != null){

            $form = $this->createForm(ProfileEditType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                                
                $this->getDoctrine()->getManager()->flush();

                $this->addFlash("success", $translator->trans('file.usermaj'));
                return $this->redirectToRoute('profile');
            }

            return $this->render('profile/profile-edit.html.twig', [
                'form_editProfile' => $form->createView(),
            ]);
        }
        else{
            $this->addFlash("danger", $translator->trans('file.error'));
            return $this->redirectToRoute('index');
        }

    }

}