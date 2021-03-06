<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\UserType;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;


use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;





class UserController extends AbstractController
{

    /**
     * @Route("/register", name="registerPath")
     */
    public function register(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder , EventDispatcherInterface $eventDispatcher )
    {
        $user = new User();

        if(isset($_POST['email'])){

            $user->setUsername('user');
            $hash = $encoder->encodePassword($user, $_POST['myPassword']);
            $user->setPassword($hash);
            $user->setMail($_POST['email']);

            $manager->persist($user);
            $manager->flush();



        // Here, "public" is the name of the firewall in your security.yml
        $token = new UsernamePasswordToken($user, $user->getPassword(), "main", $user->getRoles());

        // For older versions of Symfony, use security.context here
        $this->get("security.token_storage")->setToken($token);

        // Fire the login event
        // Logging the user in above the way we do it doesn't do this automatically
        $event = new InteractiveLoginEvent($request, $token);
        $eventDispatcher->dispatch($event, "security.interactive_login");

        // maybe redirect out here
    


            return $this->redirectToRoute('avatar');


        }



        return $this->render('user/register.html.twig');
    }




     /**
     * @Route("/avatar", name="avatar")
     */
    public function avatar()
    {
            return $this->render('user/avatar.html.twig');
     }

     
     /**
     * @Route("/storeAvatar/{avatar}", name="storeAvatarPath")
     */

     public function storeAvatar(EntityManagerInterface $manager, UserInterface $user, $avatar)
     {
        

        $user->setAvatar($avatar);
        $user->setAvatarAssetSrc('images/'. $avatar . '.png');


        $manager->persist($user);
        $manager->flush();


        return $this->render("user/demonstration.html.twig");

           

     }




     /**
     * @Route("/associatedMail", name="associatedMailPath")
     */
    public function associatedMail( EntityManagerInterface $manager, UserInterface $user)
    {
        if(isset($_POST['submit'])){

            $user->setMailing('on');

            $user->setAssociatedMail($_POST['assocMail']);

            $manager->persist($user);

            $manager->flush();

            return $this->render('user/demonstration.html.twig');
            
        }

            $manager->persist($user);
            $manager->flush();
    

            return $this->render('user/associatedMail.html.twig');
     }


    /**
     * @Route("/login", name="loginPath")
     */


    public function login(){
       
        return $this->render('user/login.html.twig');

    }

    /**
     * @Route("/logout", name="logoutPath")
     */


    public function logout(){
       
     //logout path
    }


    /**
     * @Route("/profilePage", name="profilePagePath")
     */


    public function profilePage(UserInterface $user){

       
        $projects = $user->getProjects();

        $finishedProjects = 0;

        $unfinishedProjects = 0;

        $dynamic = $user->getDynamic();



        foreach($projects as $project){

            if( $project->getTotalCountDone() == 'true'){
               
                $finishedProjects+=1;

            } elseif( $project->getTotalCountDone() == 'false'){

                $unfinishedProjects += 1;

            }
        }


        $daysOnTheApp = $user->getDaysOnTheApp();

        $daysWithCountDone = $user->getAllDailyCountsDone();


        if($daysOnTheApp != 0 ){

            $dailyCountAverage = floor(($daysWithCountDone/$daysOnTheApp)*100);



        } else {

            $dailyCountAverage = 0;
        }


        $totalWork =  0;

        
        foreach($projects as $project){

            $totalWork += $project->getTotalCount();
        }


        //contrary to the dailyCountAverage, we start the count as 1

        $averageWorkTime = floor((($totalWork/($daysOnTheApp+1)))/60);

       



        return $this->render('user/profilePage.html.twig',['finishedProjects' => $finishedProjects, 'unfinishedProjects' =>  $unfinishedProjects , 'dynamic' => $dynamic, 'dailyCountAverage' => $dailyCountAverage, 'averageWorkTime' => $averageWorkTime]);

    }


   

    /**
     * @Route("/checkUsers", name="checkUsersPath")
     */


    public function checkUsers(){

        if(isset($_POST["userMail"])){


            $users = $this->getDoctrine()->getRepository(User::class)->findAll();


            $mailUsed = false;


            foreach($users as $user){


                if( $user->getMail() == $_POST["userMail"]){


                    $mailUsed = true;
                }
            }

            return new JsonResponse(['mailUsed' => $mailUsed]);


        }


        return new JsonResponse(['error' => 'error']);



    }



    
    /**
     * @Route("/loginCheck", name="loginCheckPath")
     */


    public function loginCheck(UserPasswordEncoderInterface $encoder){

        if(isset($_POST["userMail"])){


            $users = $this->getDoctrine()->getRepository(User::class)->findAll();


            $validity = false;


            foreach($users as $user){


                if(( $user->getMail() == $_POST["userMail"]) && ($encoder->isPasswordValid($user, $_POST["userPass"]))){

                    $validity = true;

                }
            }

            return new JsonResponse(['validity' =>  $validity]);


        }


        return new JsonResponse(['error' => 'error']);



    }



    

    /**
     * @Route("/profilefront", name="profiletest")
     */




    public function profilePageFront (UserInterface $user){

       
        $projects = $user->getProjects();

        $finishedProjects = 0;

        $unfinishedProjects = 0;

        $dynamic = $user->getDynamic();



        foreach($projects as $project){

            if( $project->getTotalCountDone() == 'true'){
               
                $finishedProjects+=1;

            } elseif( $project->getTotalCountDone() == 'false'){

                $unfinishedProjects += 1;

            }
        }


        $daysOnTheApp = $user->getDaysOnTheApp();

        $daysWithCountDone = $user->getAllDailyCountsDone();


        if($daysOnTheApp != 0 ){

            $dailyCountAverage = floor(($daysWithCountDone/$daysOnTheApp)*100);



        } else {

            $dailyCountAverage = 0;
        }


        $totalWork =  0;

        
        foreach($projects as $project){

            $totalWork += $project->getTotalCount();
        }


        //contrary to the dailyCountAverage, we start the count as 1

        $averageWorkTime = floor((($totalWork/($daysOnTheApp+1)))/60);

       



        return $this->render('user/profilePage2.html.twig',['finishedProjects' => $finishedProjects, 'unfinishedProjects' =>  $unfinishedProjects , 'dynamic' => $dynamic, 'dailyCountAverage' => $dailyCountAverage, 'averageWorkTime' => $averageWorkTime]);



    }



    




    
}
