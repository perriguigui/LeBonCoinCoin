<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\CreateAccountType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $data = ['last_username' => $lastUsername, 'error' => $error];
        return $this->render('user/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]); // on envoie ensuite le formulaire au template
        //return new JsonResponse(['last_username' => $lastUsername, 'error' => $error]) ;
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }
    /**
     * @Route("/register", name="CreateAccount")
     */
    public function register(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder)
    {
        $user = new user();
        $form = $this->createForm(CreateAccountType::class,$user);
        $form->handleRequest($request); // On récupère le formulaire envoyé dans la requête
        if ($form->isSubmitted() && $form->isValid()) { // on véfifie si le formulaire est envoyé et si il est valide
            $article = $form->getData(); // On récupère l'article associé
            $encoded = $encoder->encodePassword($article, $article->getPassword());

            /**if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

            // Move the file to the directory where brochures are stored
            try {
            $imageFile->move(
            $this->getParameter('upload_directory'),
            $newFilename
            );
            } catch (FileException $e) {
            // ... handle exception if something happens during file upload
            }

            // updates the 'brochureFilename' property to store the PDF file name
            // instead of its contents
            $article->setBrochureFilename($newFilename);
            }*/

            $article->setPassword($encoded);
            $article->setCreationdate(New \DateTime());
            $article->setRoles(['ROLE_USER']);
            $article->setChangedate(New \DateTime());

            $em->persist($article); // on le persiste
            $em->flush(); // on save

            return $this->redirectToRoute('index'); // Hop redirigé et on sort du controller
        }
        return $this->render('user/register.html.twig', ['form' => $form->createView()]); // on envoie ensuite le formulaire au template

    }

    /**
     * @Route("/profile/{id}", name="profile", methods={"GET"})
     */
    public function UserInfo(EntityManagerInterface $em, $id)
    {
        $repository = $em->getRepository(User::class);
        $user = $repository->find($id);
        if(!$user) {
            throw $this->createNotFoundException('Sorry, there is no user with this id');
        }
        return $this->render('profile.html.twig', [
            "user" => $user
        ]);
    }
}
