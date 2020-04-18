<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileEditType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function editProfile(Request $request, User $user=null)
    {

        if( $user != null){

            $form = $this->createForm(ProfileEditType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                                
                $this->getDoctrine()->getManager()->flush();

                $this->addFlash("success", 'Profile Updated');
                return $this->redirectToRoute('profile');
            }

            return $this->render('profile/profile-edit.html.twig', [
                'form_editProfile' => $form->createView(),
            ]);
        }
        else{
            $this->addFlash("danger", 'Error');
            return $this->redirectToRoute('index');
        }

    }

    /**
     * @Route("/profileDelete/{id}", name="profileDelete")
     */
    public function profileDelete(Request $request, User $user) :Response
    {

        if( $user == $this->getUser()){

            //sécurité validation de suppression de compte
            if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {

                //sécurité pour ne pas supprimer user le plus gradé
                if( $user->hasRole('ROLE_SUPER_ADMIN'))
                {
                    $this->addFlash("danger", 'Can\'t delete your profile you are a SUPER ADMIN');
                    return $this->redirectToRoute('profile');     
                }

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($user);
                $entityManager->flush();

                $this->addFlash("success", 'Profile Deleted');
                return $this->redirectToRoute('index');       
            }
            else{
                $this->addFlash("danger", 'Error');
                return $this->redirectToRoute('profile');
            }
        }
        else{
            $this->addFlash("danger", 'Error');
            return $this->redirectToRoute('index');
        }

    }
}
