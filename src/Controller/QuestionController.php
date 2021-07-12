<?php

namespace App\Controller;

use App\service\MarkdownHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Question;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class QuestionController extends AbstractController
{
    /**
     * @Route("/", name="app_homepage")
     */
    public function homepage(EntityManagerInterface $entityManager)
    {
        /*
        // fun example of using the Twig service directly!
        $html = $twigEnvironment->render('question/homepage.html.twig');

        return new Response($html);
        */
        $repository = $entityManager->getRepository(Question::class);
        $questions = $repository->findAllAskedOrderedByNewest();
        return $this->render('question/homepage.html.twig',[
            'questions'=>$questions,
        ]);
    }

    /**
     * @Route("/questions/new")
     */
    public function new(EntityManagerInterface $entityManager)
    {
            return new Response('Sounds like a great feature for V2');
    }

    /**
     * @Route("/questions/{slug}", name="app_question_show")
     */
    public function show($slug,MarkdownHelper $markdownHelper,EntityManagerInterface $entityManager)
    {
        $repository = $entityManager->getRepository(Question::class);
        /** @var Question | null $question  */
        $question = $repository->findOneBy(['slug'=> $slug]);
        if(!$question){
            throw $this->createNotFoundException(sprintf('no question found for slug "%s"',$slug));
        }

        $answers = [
            'Make sure your cat is sitting `purrrfectly` still 🤣',
            'Honestly, I like furry shoes better than MY cat',
            'Maybe... try saying the spell backwards?',
        ];

        return $this->render('question/show.html.twig', [
            'question' => $question,
            'answers' => $answers,
        ]);
    }

    /**
     * @Route("/questions/{slug}/vote", name="app_question_vote", methods="POST")
     */
    public function questionVote(Question $question,Request $request,EntityManagerInterface $entityManager)
    {
        $direction = $request->request->get('direction');
        if($direction==='up'){
            $question->upVote();
        }
        if($direction==='down'){
            $question->downVote();
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_question_show',[
            'slug'=>$question->getSlug(),
        ]);
    }
}
