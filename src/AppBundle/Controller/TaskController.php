<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Task;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session;



/**
 * Task controller.
 *
 * @Route("task")
 */
class TaskController extends Controller
{
    /**
     * Lists all task entities.
     *
     * @Route("/", name="task_index")
     * @Method("GET")
     */
    public function indexAction(Request $request){
        return $this->render('task/index.html.twig');
    }


    /**
     * @Route("/filterlist", name="filterlist")
     */
    public function filterlistAction(Request $request){
        $status = 0;
        if ($request->isXMLHttpRequest()) {
            $status = $request->request->get('status');
        }

        $em = $this->getDoctrine()->getManager();
        if($status == 0){
            $tasks = $em->getRepository('AppBundle:Task')->findAll();
        }else{
            $tasks = $em->getRepository('AppBundle:Task')->findBy(array('status'=>$status));
        }

        $left_task = $em->getRepository('AppBundle:Task')->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.status = 1')
            ->getQuery()
            ->getSingleScalarResult();

        $completed_task = $em->getRepository('AppBundle:Task')->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.status = 2')
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render('task/partial_list.html.twig', array(
            'tasks' => $tasks,
            'left_task' => $left_task,
            'completed_task' => $completed_task,
        ));
    }

    /**
     * @Route("/todolist", name="todolist")
     */
    public function todolistAction(){
        $em = $this->getDoctrine()->getManager();
        $tasks = $em->getRepository('AppBundle:Task')->findAll();

        $left_task = $em->getRepository('AppBundle:Task')->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.status = 1')
            ->getQuery()
            ->getSingleScalarResult();

        $completed_task = $em->getRepository('AppBundle:Task')->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.status = 2')
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render('task/partial_list.html.twig', array(
            'tasks' => $tasks,
            'left_task' => $left_task,
            'completed_task' => $completed_task,
        ));
    }


    /**
     * @Route("/addtask", name="addtask")
     */
    public function addtaskAction(Request $request){

        if ($request->isMethod('POST')) {
            if ($request->isXMLHttpRequest()) {

                $em = $this->getDoctrine()->getManager();
                $name = $request->request->get('task_name');

                if(!empty($name)) {

                    $task = new Task();
                    $task->setName($name);
                    $task->setStatus(1);
                    $em->persist($task);
                    $em->flush();
                }

                return  $this->todolistAction();
            }
        }
        return new Response('This is not ajax!', 400);
    }

    /**
     * @Route("/updatetask", name="updatetask")
     */
    public function updatetaskAction(Request $request){

        if ($request->isMethod('POST')) {
            if ($request->isXMLHttpRequest()) {
                $em = $this->getDoctrine()->getManager();

                $status = $request->request->get('status');
                $id = $request->request->get('id');

                $em = $this->getDoctrine()->getEntityManager();
                $repo =$em->getRepository('AppBundle:Task');


                if( $id == 0 ){
                    $status = $request->request->get('status');
                    foreach ( $repo->findAll() as $task ) {
                        $task->setStatus($status);
                        $em->persist($task);
                    }
                    $em->flush();

                }else{
                    $entity = $repo->findOneBy(array('id' => $id));

                    if ($entity != null) {
                        $entity->setStatus($status);
                        $em->persist($entity);
                        $em->flush();
                    }
                }
                return  $this->todolistAction();
            }

        }
        return new Response('This is not ajax!', 400);
    }

    /**
     * @Route("/deletetask", name="deletetask")
     */
    public function deletetaskAction(Request $request){

        if ($request->isMethod('POST')) {

            if ($request->isXMLHttpRequest()) {

                $id = $request->request->get('id');

                $em = $this->getDoctrine()->getEntityManager();
                $repo =$em->getRepository('AppBundle:Task');

                if( $id == 0 ){

                    $status = $request->request->get('status');
                    foreach ( $repo->findBy(array('status'=> 2 )) as $task ) {
                        $em->remove($task);
                    }
                    $em->flush();

                }else{

                    $entity = $repo->findOneBy(array('id' => $id));
                    if ($entity != null) {
                        $em->remove($entity);
                        $em->flush();
                    }
                }

                return $this->todolistAction();
            }
        }
        return new Response('This is not ajax!', 400);
    }

}
