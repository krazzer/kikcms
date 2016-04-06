<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LuckyController extends Controller
{
	/**
	 * @Route("/lucky/number/{count}")
	 * @param $count
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function numberAction($count)
	{
		$numbers = array();
		for ($i = 0; $i < $count; $i++) {
			$numbers[] = rand(0, 100);
		}
		$numbersList = implode(', ', $numbers);

		return $this->render('lucky/number.html.twig', [
			'luckyNumberList' => $numbersList
		]);
	}
}