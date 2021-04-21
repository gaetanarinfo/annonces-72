<?php

namespace App\Controller;

use App\Entity\Annonces;
use App\Entity\Comment;
use App\Entity\Contact;
use App\Entity\LikeAnnonces;
use App\Entity\User;
use App\Entity\VoteAnnonces;
use App\Form\CommentType;
use App\Form\ContactType;
use App\Notification\ContactNotification;
use App\Repository\AnnoncesRepository;
use App\Repository\CommentRepository;
use App\Repository\LikeAnnoncesRepository;
use App\Repository\UserRepository;
use App\Repository\VoteAnnoncesRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class AnnoncesController extends AbstractController
{

    /**
     * @Route("/annonce/{id}", name="annonce.show") requirements={"id": [0-9\-]""}
     * @return Responce
    */
    public function show (Annonces $annonces, Request $request, ContactNotification $notif, UserRepository $repository, AnnoncesRepository $repositoryAnnonces, CommentRepository $repositoryComment, VoteAnnoncesRepository $repositoryVote, LikeAnnoncesRepository $repositoryLike):Response{

        $author = $annonces->getAuthor();
        $authorId = $this->getUser();
        $countAnnonces = $repositoryAnnonces->findCount($author);
        $annonceSimilaire = $repositoryAnnonces->findSimilaire($annonces->getCategory());
        $annonceLatest = $repositoryAnnonces->findLatest();

        $contact = new Contact();
        $formContact = $this->createForm(ContactType::class, $contact);
        $formContact->handleRequest($request);

        $user = $repository->findOneBy(array('username' => $author));

        if ($formContact->isSubmitted() && $formContact->isValid()) {
           
            $notif->notify($contact);
            $this->addFlash('success', 'Votre message à bien été transmis');
            return $this->redirectToRoute('home');

        }

        $comment = new Comment();
        $formComment = $this->createForm(CommentType::class, $comment);
        $formComment->handleRequest($request);
        $comments = $repositoryComment->findLatest($annonces->getId());
        $countComment = $repositoryComment->findCount($annonces->getId());

        if ($formComment->isSubmitted() && $formComment->isValid()) {
            $comment->setAuthor($author);
            $comment->setAnnonceId($annonces->getId());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();
            $this->addFlash('success', 'Votre commentaire à été poster');
            return $this->render('pages/annonce_type.html.twig', [
                'formContact' => $formContact->createView(),
                'annonces' => $annonces,
                'user' => $user,
                'count' => $countAnnonces,
                'annonceSimilaire' => $annonceSimilaire,
                'annonceLatest' => $annonceLatest,
                'countComment' => $countComment,
                'comment' => $comments,
                'formComment' => $formComment->createView()
            ]);

    
        }

        if($authorId == null)
        {
            $voteId = 0;
            $likeId = 0;
        }else{
            $voteId = $repositoryVote->findUserVote($authorId->getId());
            $likeId = $repositoryLike->findUserLike($annonces->getId());

        }

        $voteCount = $repositoryVote->findCount($annonces->getId());

        return $this->render('pages/annonce_type.html.twig', [
            'formContact' => $formContact->createView(),
            'annonces' => $annonces,
            'user' => $user,
            'count' => $countAnnonces,
            'annonceSimilaire' => $annonceSimilaire,
            'annonceLatest' => $annonceLatest,
            'countComment' => $countComment,
            'comment' => $comments,
            'formComment' => $formComment->createView(),
            'voteId' => $voteId,
            'voteCount' => $voteCount,
            'likeId' => $likeId,
        ]);

    }

    /**
     * @Route("/annonce/vote/stars/1/{id}", name="annonce.starsVote1") requirements={"id": [0-9\-]""}
     * @return Responce
    */
    public function starsVote1(Annonces $annonces, Request $request, ContactNotification $notif, UserRepository $repository, AnnoncesRepository $repositoryAnnonces, CommentRepository $repositoryComment, VoteAnnoncesRepository $repositoryVote)
    {
        $author = $annonces->getAuthor();

        $countAnnonces = $repositoryAnnonces->findCount($author);
        $annonceSimilaire = $repositoryAnnonces->findSimilaire($annonces->getCategory());
        $annonceLatest = $repositoryAnnonces->findLatest();

        $contact = new Contact();
        $formContact = $this->createForm(ContactType::class, $contact);
        $formContact->handleRequest($request);

        $comment = new Comment();
        $formComment = $this->createForm(CommentType::class, $comment);
        $formComment->handleRequest($request);
        $comments = $repositoryComment->findLatest($annonces->getId());
        $countComment = $repositoryComment->findCount($annonces->getId());

        $user = $repository->findOneBy(array('username' => $author));

        if ($formContact->isSubmitted() && $formContact->isValid()) {
           
            $notif->notify($contact);
            $this->addFlash('success', 'Votre message à bien été transmis');
            return $this->redirectToRoute('home');

        }

        if ($formComment->isSubmitted() && $formComment->isValid()) {
            $comment->setAuthor($author);
            $comment->setAnnonceId($annonces->getId());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();
            $this->addFlash('success', 'Votre commentaire à été poster');
            return $this->render('pages/annonce_type.html.twig', [
                'formContact' => $formContact->createView(),
                'annonces' => $annonces,
                'user' => $user,
                'count' => $countAnnonces,
                'annonceSimilaire' => $annonceSimilaire,
                'annonceLatest' => $annonceLatest,
                'countComment' => $countComment,
                'comment' => $comments,
                'formComment' => $formComment->createView()
            ]);  
        }

            $vote = new VoteAnnonces();

           
            $this->addFlash('success', 'Votre vote à été pris en compte');
            $vote->setUserId($this->getUser()->getId());
            $vote->setAnnoncesId($annonces->getId());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($vote);
            $entityManager->flush();
            return $this->redirectToRoute('annonce.show', [ 'id' => $annonces->getId() ]);

        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/annonce/vote/stars/2/{id}", name="annonce.starsVote2") requirements={"id": [0-9\-]""}
     * @return Responce
    */
    public function starsVote2(Annonces $annonces, Request $request, ContactNotification $notif, UserRepository $repository, AnnoncesRepository $repositoryAnnonces, CommentRepository $repositoryComment, VoteAnnoncesRepository $repositoryVote)
    {
        $author = $annonces->getAuthor();

        $countAnnonces = $repositoryAnnonces->findCount($author);
        $annonceSimilaire = $repositoryAnnonces->findSimilaire($annonces->getCategory());
        $annonceLatest = $repositoryAnnonces->findLatest();

        $contact = new Contact();
        $formContact = $this->createForm(ContactType::class, $contact);
        $formContact->handleRequest($request);

        $comment = new Comment();
        $formComment = $this->createForm(CommentType::class, $comment);
        $formComment->handleRequest($request);
        $comments = $repositoryComment->findLatest($annonces->getId());
        $countComment = $repositoryComment->findCount($annonces->getId());

        $user = $repository->findOneBy(array('username' => $author));

        if ($formContact->isSubmitted() && $formContact->isValid()) {
           
            $notif->notify($contact);
            $this->addFlash('success', 'Votre message à bien été transmis');
            return $this->redirectToRoute('home');

        }

        if ($formComment->isSubmitted() && $formComment->isValid()) {
            $comment->setAuthor($author);
            $comment->setAnnonceId($annonces->getId());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();
            $this->addFlash('success', 'Votre commentaire à été poster');
            return $this->render('pages/annonce_type.html.twig', [
                'formContact' => $formContact->createView(),
                'annonces' => $annonces,
                'user' => $user,
                'count' => $countAnnonces,
                'annonceSimilaire' => $annonceSimilaire,
                'annonceLatest' => $annonceLatest,
                'countComment' => $countComment,
                'comment' => $comments,
                'formComment' => $formComment->createView()
            ]);  
        }

            $vote = new VoteAnnonces();

           
            $this->addFlash('success', 'Votre vote à été pris en compte');
            $vote->setUserId($this->getUser()->getId());
            $vote->setAnnoncesId($annonces->getId());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($vote);
            $entityManager->flush();
            return $this->redirectToRoute('annonce.show', [ 'id' => $annonces->getId() ]);

        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/annonce/vote/stars/3/{id}", name="annonce.starsVote3") requirements={"id": [0-9\-]""}
     * @return Responce
    */
    public function starsVote3(Annonces $annonces, Request $request, ContactNotification $notif, UserRepository $repository, AnnoncesRepository $repositoryAnnonces, CommentRepository $repositoryComment, VoteAnnoncesRepository $repositoryVote)
    {
        $author = $annonces->getAuthor();

        $countAnnonces = $repositoryAnnonces->findCount($author);
        $annonceSimilaire = $repositoryAnnonces->findSimilaire($annonces->getCategory());
        $annonceLatest = $repositoryAnnonces->findLatest();

        $contact = new Contact();
        $formContact = $this->createForm(ContactType::class, $contact);
        $formContact->handleRequest($request);

        $comment = new Comment();
        $formComment = $this->createForm(CommentType::class, $comment);
        $formComment->handleRequest($request);
        $comments = $repositoryComment->findLatest($annonces->getId());
        $countComment = $repositoryComment->findCount($annonces->getId());

        $user = $repository->findOneBy(array('username' => $author));

        if ($formContact->isSubmitted() && $formContact->isValid()) {
           
            $notif->notify($contact);
            $this->addFlash('success', 'Votre message à bien été transmis');
            return $this->redirectToRoute('home');

        }

        if ($formComment->isSubmitted() && $formComment->isValid()) {
            $comment->setAuthor($author);
            $comment->setAnnonceId($annonces->getId());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();
            $this->addFlash('success', 'Votre commentaire à été poster');
            return $this->render('pages/annonce_type.html.twig', [
                'formContact' => $formContact->createView(),
                'annonces' => $annonces,
                'user' => $user,
                'count' => $countAnnonces,
                'annonceSimilaire' => $annonceSimilaire,
                'annonceLatest' => $annonceLatest,
                'countComment' => $countComment,
                'comment' => $comments,
                'formComment' => $formComment->createView()
            ]);  
        }

            $vote = new VoteAnnonces();

           
            $this->addFlash('success', 'Votre vote à été pris en compte');
            $vote->setUserId($this->getUser()->getId());
            $vote->setAnnoncesId($annonces->getId());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($vote);
            $entityManager->flush();
            return $this->redirectToRoute('annonce.show', [ 'id' => $annonces->getId() ]);

        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/annonce/vote/stars/4/{id}", name="annonce.starsVote4") requirements={"id": [0-9\-]""}
     * @return Responce
    */
    public function starsVote4(Annonces $annonces, Request $request, ContactNotification $notif, UserRepository $repository, AnnoncesRepository $repositoryAnnonces, CommentRepository $repositoryComment, VoteAnnoncesRepository $repositoryVote)
    {
        $author = $annonces->getAuthor();

        $countAnnonces = $repositoryAnnonces->findCount($author);
        $annonceSimilaire = $repositoryAnnonces->findSimilaire($annonces->getCategory());
        $annonceLatest = $repositoryAnnonces->findLatest();

        $contact = new Contact();
        $formContact = $this->createForm(ContactType::class, $contact);
        $formContact->handleRequest($request);

        $comment = new Comment();
        $formComment = $this->createForm(CommentType::class, $comment);
        $formComment->handleRequest($request);
        $comments = $repositoryComment->findLatest($annonces->getId());
        $countComment = $repositoryComment->findCount($annonces->getId());

        $user = $repository->findOneBy(array('username' => $author));

        if ($formContact->isSubmitted() && $formContact->isValid()) {
           
            $notif->notify($contact);
            $this->addFlash('success', 'Votre message à bien été transmis');
            return $this->redirectToRoute('home');

        }

        if ($formComment->isSubmitted() && $formComment->isValid()) {
            $comment->setAuthor($author);
            $comment->setAnnonceId($annonces->getId());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();
            $this->addFlash('success', 'Votre commentaire à été poster');
            return $this->render('pages/annonce_type.html.twig', [
                'formContact' => $formContact->createView(),
                'annonces' => $annonces,
                'user' => $user,
                'count' => $countAnnonces,
                'annonceSimilaire' => $annonceSimilaire,
                'annonceLatest' => $annonceLatest,
                'countComment' => $countComment,
                'comment' => $comments,
                'formComment' => $formComment->createView()
            ]);  
        }

            $vote = new VoteAnnonces();

           
            $this->addFlash('success', 'Votre vote à été pris en compte');
            $vote->setUserId($this->getUser()->getId());
            $vote->setAnnoncesId($annonces->getId());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($vote);
            $entityManager->flush();
            return $this->redirectToRoute('annonce.show', [ 'id' => $annonces->getId() ]);

        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/annonce/vote/stars/5/{id}", name="annonce.starsVote5") requirements={"id": [0-9\-]""}
     * @return Responce
    */
    public function starsVote5(Annonces $annonces, Request $request, ContactNotification $notif, UserRepository $repository, AnnoncesRepository $repositoryAnnonces, CommentRepository $repositoryComment, VoteAnnoncesRepository $repositoryVote)
    {
        $author = $annonces->getAuthor();

        $countAnnonces = $repositoryAnnonces->findCount($author);
        $annonceSimilaire = $repositoryAnnonces->findSimilaire($annonces->getCategory());
        $annonceLatest = $repositoryAnnonces->findLatest();

        $contact = new Contact();
        $formContact = $this->createForm(ContactType::class, $contact);
        $formContact->handleRequest($request);

        $comment = new Comment();
        $formComment = $this->createForm(CommentType::class, $comment);
        $formComment->handleRequest($request);
        $comments = $repositoryComment->findLatest($annonces->getId());
        $countComment = $repositoryComment->findCount($annonces->getId());

        $user = $repository->findOneBy(array('username' => $author));

        if ($formContact->isSubmitted() && $formContact->isValid()) {
           
            $notif->notify($contact);
            $this->addFlash('success', 'Votre message à bien été transmis');
            return $this->redirectToRoute('home');

        }

        if ($formComment->isSubmitted() && $formComment->isValid()) {
            $comment->setAuthor($author);
            $comment->setAnnonceId($annonces->getId());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();
            $this->addFlash('success', 'Votre commentaire à été poster');
            return $this->render('pages/annonce_type.html.twig', [
                'formContact' => $formContact->createView(),
                'annonces' => $annonces,
                'user' => $user,
                'count' => $countAnnonces,
                'annonceSimilaire' => $annonceSimilaire,
                'annonceLatest' => $annonceLatest,
                'countComment' => $countComment,
                'comment' => $comments,
                'formComment' => $formComment->createView()
            ]);  
        }

            $vote = new VoteAnnonces();

           
            $this->addFlash('success', 'Votre vote à été pris en compte');
            $vote->setUserId($this->getUser()->getId());
            $vote->setAnnoncesId($annonces->getId());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($vote);
            $entityManager->flush();
            return $this->redirectToRoute('annonce.show', [ 'id' => $annonces->getId() ]);

        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/annonce/vote/like/{id}", name="annonce.like") requirements={"id": [0-9\-]""}
     * @return Responce
    */
    public function like(Annonces $annonces, Request $request, ContactNotification $notif, UserRepository $repository, AnnoncesRepository $repositoryAnnonces, CommentRepository $repositoryComment, VoteAnnoncesRepository $repositoryVote)
    {
        $author = $annonces->getAuthor();

        $countAnnonces = $repositoryAnnonces->findCount($author);
        $annonceSimilaire = $repositoryAnnonces->findSimilaire($annonces->getCategory());
        $annonceLatest = $repositoryAnnonces->findLatest();

        $contact = new Contact();
        $formContact = $this->createForm(ContactType::class, $contact);
        $formContact->handleRequest($request);

        $comment = new Comment();
        $formComment = $this->createForm(CommentType::class, $comment);
        $formComment->handleRequest($request);
        $comments = $repositoryComment->findLatest($annonces->getId());
        $countComment = $repositoryComment->findCount($annonces->getId());

        $user = $repository->findOneBy(array('username' => $author));

        if ($formContact->isSubmitted() && $formContact->isValid()) {
           
            $notif->notify($contact);
            $this->addFlash('success', 'Votre message à bien été transmis');
            return $this->redirectToRoute('home');

        }

        if ($formComment->isSubmitted() && $formComment->isValid()) {
            $comment->setAuthor($author);
            $comment->setAnnonceId($annonces->getId());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();
            $this->addFlash('success', 'Votre commentaire à été poster');
            return $this->render('pages/annonce_type.html.twig', [
                'formContact' => $formContact->createView(),
                'annonces' => $annonces,
                'user' => $user,
                'count' => $countAnnonces,
                'annonceSimilaire' => $annonceSimilaire,
                'annonceLatest' => $annonceLatest,
                'countComment' => $countComment,
                'comment' => $comments,
                'formComment' => $formComment->createView()
            ]);  
        }

            $like = new LikeAnnonces();

           
            $this->addFlash('success', 'Vous aimez cette annonce');
            $like->setUserId($this->getUser()->getId());
            $like->setAnnoncesId($annonces->getId());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($like);
            $entityManager->flush();
            return $this->redirectToRoute('annonce.show', [ 'id' => $annonces->getId() ]);

        return $this->redirectToRoute('home');
    }

}
