<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
* @Route("/superadmin")
*/
class SuperAdminController extends AbstractController
{
    /**
     * @Route("/", name="super_admin")
     */
    public function index(UserRepository $userRepository )
    {
        return $this->render('admin/super_admin/index.html.twig', [
            'users' => $userRepository->findUserByNewest(),
        ]);
    }

    /**
     * @Route("/{id}/editRole", name="user_editRole")
     */
    public function editRole(User $user = null, TranslatorInterface $translator)
    {   
        //vérification si user existant
        if ($user == null){
            return $this->redirectToRoute('super_admin');
        }
        //role changer en membre si l'user est super admin
        if ( $user->hasRole('ROLE_SUPER_ADMIN')){
            $user->setRoles( ['ROLE_USER'] ); 
        }
        else{
            //membre devient super admin
            $user->setRoles( ['ROLE_USER','ROLE_ADMIN','ROLE_SUPER_ADMIN'] );
        }       
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $this->addFlash("success", $translator->trans('file.rolec'));
        return $this->redirectToRoute('super_admin');

    }

    /**
     * @Route("/{id}/editRolePerm", name="editRolePerm")
     */
    public function editRolePerm(User $user = null, TranslatorInterface $translator)
    {
        //vérification si user existant
        if ($user == null){
            return $this->redirectToRoute('super_admin');
        }
        //role changé a membre
        if ($user->hasRole('ROLE_ADMIN') ){

            $user->setRoles( ['ROLE_USER'] );
           
        }
        else{
            //role changé a admin
            $user->setRoles( ['ROLE_USER','ROLE_ADMIN'] );  
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $this->addFlash("success", $translator->trans('file.rolec'));
        return $this->redirectToRoute('super_admin');

    }


}
