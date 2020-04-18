<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\H\Request;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\Routing\Annotation\Route;

/**
* @Route("/superadmin")
*/
class SuperAdminController extends AbstractController
{
    /**
     * @Route("/", name="super_admin")
     */
    public function index(UserRepository $userRepository)
    {
        return $this->render('admin/super_admin/index.html.twig', [
            'users' => $userRepository->findBy(array(), array('id' => 'desc')),
        ]);
    }

    /**
     * @Route("/{id}/userDelete", name="user_delete")
     */
    public function delete(HttpFoundationRequest $request, User $user)
    {
        if($user->hasRole('ROLE_SUPER_ADMIN'))
        {
            $this->addFlash("danger", "can't delete super administrator");
            return $this->redirectToRoute('super_admin');
        }

        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();

            $check=$entityManager->getRepository(User::class)->findAll();
            if($check == 1 ){
                return $this->redirectToRoute('super_admin');
            }

            $entityManager->remove($user);
            $entityManager->flush();
        }

        $this->addFlash("success", "User deleted");
        return $this->redirectToRoute('super_admin');
    }

    /**
     * @Route("/{id}/editRole", name="user_editRole")
     */
    public function editRole(User $user = null)
    {
        if ($user == null){
            return $this->redirectToRoute('super_admin');
        }

        if ( $user->hasRole('ROLE_SUPER_ADMIN')){
            $user->setRoles( ['ROLE_USER'] ); 
        }
        else{
            $user->setRoles( ['ROLE_USER','ROLE_ADMIN','ROLE_SUPER_ADMIN'] );
        }       
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $this->addFlash("success", "Permission changed");
        return $this->redirectToRoute('super_admin');

    }

    /**
     * @Route("/{id}/editRolePerm", name="editRolePerm")
     */
    public function editRolePerm(User $user = null)
    {
        if ($user == null){
            return $this->redirectToRoute('super_admin');
        }

        if ($user->hasRole('ROLE_ADMIN') ){

            $user->setRoles( ['ROLE_USER'] );
           
        }
        else{
            $user->setRoles( ['ROLE_USER','ROLE_ADMIN'] );  
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $this->addFlash("success", "Role modifié");
        return $this->redirectToRoute('super_admin');

    }


}
