<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\EditProfileType;
use App\Form\EditPasswordType;
;
use App\Repository\OrderRepository;
use App\Repository\CalendarRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Repository\SubOrderRepository;
// test
class MemberController extends AbstractController
{
    /**
     * @Route("/my-account", name="app_account")
     */
    public function index(OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();
       
        $lastOrder = $orderRepository->findOneBy(['user' => $user], ['id' => 'desc']);
        dump($lastOrder);
        return $this->render('member/index.html.twig', [
            'lastOrder' => $lastOrder
        ]);
    }

    /**
     * @Route("/my-account/my-orders", name="app_account_orders")
     */
    public function commandes(OrderRepository $orderRepository, SubOrderRepository $subOrderRepository): Response
    {
        $user = $this->getUser();
        $orders = $user->getOrders()->getValues();

        $listeCommandes = $orderRepository->findBy(
            ['user' => $user],
            []
        );

        $tablisteproduit = [];
        for ($i = 0; $i < count($orders); $i++) {

            $details = $orders[$i]->getOrderDetails();
            $tablisteproduit[$i] = $listeproduit = $subOrderRepository->findBy(
                ['orderDetails' => $details],
                []
            );
        }
        
        return $this->render('member/orders.html.twig', [
            'orders' => $listeCommandes,
            'tablisteproduit'=>$tablisteproduit
        ]);
    }

    /**
     * @Route("/my-account/my-appointments", name="app_account_rdv")
     */
    public function rendezVous(CalendarRepository $calendarRepository): Response
    {
        $user = $this->getUser();
        dump($user);
        $listeRdv = $calendarRepository->findBy(
            ['user' => $user],
            []
        );
        dump($listeRdv);
        return $this->render('member/services.html.twig', [
            'rdvs' => $listeRdv
        ]);
    }

    /**
     * @Route("/my-account/edit", name="app_account_edit" , methods={"GET","POST"})
     */
    public function edit(request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(EditProfileType::class, $user);
        dump($user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('message', 'profil mis à jour');
            // return $this->redirectToRoute('modifier');
        }

        
        $formPassword = $this->createForm(EditPasswordType::class);
        $formPassword->handleRequest($request);
        if($formPassword->isSubmitted() && $formPassword->isValid()){
            $entityManager = $this->getDoctrine()->getManager();
            $password = $formPassword->get('password')->getData();
            $confirmPassword = $formPassword->get('confirmPassword')->getData();
            // Verification si les deux MDP sont identique -- SECURITE
            if($password == $confirmPassword){
                $user->setPassword($passwordEncoder->encodePassword($user, $password));
                $entityManager->flush();
                $this->addFlash('message', 'mot de passe a été mis à jour');
                return $this->redirectToRoute('app_account_edit');
            }else{
                $this->addFlash('error', 'Les deux mots de passe ne sont pas identiques');
            }
        }

        return $this->render('member/edit.html.twig', [
            'form' => $form->createView(),
            'formPassword' => $formPassword->createView(),
        ]);
    
    }


    // /**
    //  * @Route("/mon-compte/modifier/motdepasse", name="app_account_edit_mdp" , methods={"GET","POST"})
    //  */
    // public function editPass(request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    // {
    //     if($request->isMethod('POST')) {
    //         $em = $this->getDoctrine()->getManager();
    //         $user = $this->getUser();
    //         // Verification si les deux MDP sont identique -- SECURITE
    //         if($request->request->get('pass') == $request->request->get('pass2')){
    //             $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('pass')));
    //             $em->flush();
    //             $this->addFlash('message', 'mot de passe a été mis à jour');
    //             return $this->redirectToRoute('app_account_edit');
    //         }else{
    //             $this->addFlash('error', 'Les deux mots de passe ne sont pas identiques');
    //         }
    //     }



    //     return $this->render('member/editpass.html.twig');
    
    // }
}
