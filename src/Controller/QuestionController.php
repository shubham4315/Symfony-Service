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
        $question = new Question();
        $question->setName('Missing pants')
            ->setSlug('missing-pants-'.rand(0, 1000))
            ->setQuestion(<<<EOF
                Hi! So... I'm having a *weird* day. Yesterday, I cast a spell
                to make my dishes wash themselves. But while I was casting it,
                I slipped a little and I think `I also hit my pants with the spell`.
                When I woke up this morning, I caught a quick glimpse of my pants
                opening the front door and walking out! I've been out all afternoon
                (with no pants mind you) searching for them.
                Does anyone have a spell to call your pants back?
                EOF
            );

            if (rand(1, 10) > 2) {
                $question->setAskedAt(new \DateTime(sprintf('-%d days', rand(1, 100))));
            }

            $question->setVotes(rand(-20,50));

            $entityManager->persist($question);
            $entityManager->flush();

            return new Response(sprintf(
                'Well hallo! The shiny new question is id #%d, slug: %s',
                $question->getId(),
                $question->getSlug()
            ));
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
    public function questionVote(Question $question,Request $request)
    {
        $direction = $request->request->get('direction');
        if($direction==='up'){
            $question->setVotes($question->getVotes()+1);
        }
        if($direction==='down'){
            $question->setVotes($question->getVotes()-1);
        }

        dd($question);
    }
}
