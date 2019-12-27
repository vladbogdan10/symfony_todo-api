<?php

namespace App\Controller;

use App\Entity\Task;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class TaskController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(): RedirectResponse
    {
        return $this->redirectToRoute('todo');
    }

    /**
     * @Route("/todo", name="todo")
     */
    public function todo(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to todo api',
        ]);
    }

    /**
     * @Route("todo/list", name="todo_list")
     */
    public function list(): JsonResponse
    {
        $taskList = $this->getDoctrine()
            ->getRepository(Task::class)
            ->findAll();

        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);

        $jsonData = $serializer->serialize($taskList, 'json');

        $response = JsonResponse::fromJsonString($jsonData);
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }

    /**
     * @Route("todo/create", name="todo_create", methods="POST")
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        $content = json_decode($request->getContent(), true);

        $entityManager = $this->getDoctrine()->getManager();

        $task = new Task();
        $task->setName($content['name']);
        $task->setInProgress($content['progress']);
        $task->setDone($content['done']);

        $entityManager->persist($task);
        $entityManager->flush();

        return new Response('Saved new task with id '.$task->getId());
    }

    /**
     * @Route("todo/done", name="todo_done")
     */
    public function done(): JsonResponse
    {
        return $this->json([
            'message' => '/todo/done',
        ]);
    }


    /**
     * @Route("todo/delete", name="todo_delete")
     */
    public function delete(): JsonResponse
    {
        return $this->json([
            'message' => '/todo/delete',
        ]);
    }
}
