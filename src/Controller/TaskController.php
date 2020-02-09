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
        // TODO: list of endpoints
        return $this->json([
            'message' => 'Welcome to todo api',
        ]);
    }

    /**
     * @Route("todo/list", name="todo_list", methods="GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $taskRepository = $this->getDoctrine()->getRepository(Task::class);
        $taskList = $taskRepository->findBy([], ['id'=>'DESC']);

        if ($request->query->has('last')) {
            $taskList = $taskRepository->findOneBy([], ['id' => 'DESC']);
        }

        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);

        $jsonData = $serializer->serialize($taskList, 'json');

        return JsonResponse::fromJsonString($jsonData);
    }

    /**
     * @Route("todo/create", name="todo_create", methods="POST")
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        // TODO: error handling
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
     * @Route("todo/status/{id}", name="todo_status", methods="POST")
     * @param Request $request
     * @param int|null $id
     * @return Response
     */
    public function update(Request $request, ?int $id): Response
    {
        // TODO: task name update.
        $status = $request->query->get('status');

        $entityManager = $this->getDoctrine()->getManager();

        $task = $entityManager->getRepository(Task::class)->find($id);

        if (!$task) {
            throw $this->createNotFoundException(
                'No task found for id '.$id
            );
        }

        $task->setStatus($status);

        $entityManager->flush();

        return new Response('Status changed for task with id '.$task->getId());
    }


    /**
     * @Route("todo/delete/{id}", name="todo_delete", methods="DELETE")
     * @param int|null $id
     * @return Response
     */
    public function delete(?int $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        $task = $entityManager->getRepository(Task::class)->find($id);

        $entityManager->remove($task);
        $entityManager->flush();

        return new Response('Removed task with id '.$id);
    }
}
