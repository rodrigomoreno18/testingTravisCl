<?php
namespace Tarj;

use PHPUnit\Framework\TestCase;

class TarjetitaTest extends TestCase {
	public $tarjeta;
	public $colectivo;
	public $bicicleta;
	public $boleto;
	public $tarjetaAlternativa;

	public function setUp() {
		$this->boleto = new Boleto("20-08-2016 20:00", "Colectivo", "146 Rojo", 100, 101101);
		$this->colectivo = new Colectivo("146 Rojo", "Rosario Bus");
		$this->tarjeta = new Tarjetita();
		$this->bicicleta = new Bici(1234);
	}

	public function testColectivo() {
		$cole = $this->colectivo->getLinea();
		$this->assertEquals("146 Rojo", $cole);
	}

	public function testBoleto() {
		$fecha = $this->boleto->getFecha();
		$this->assertEquals("20-08-2016 20:00", $fecha);

		$tipo = $this->boleto->getTipo();
		$this->assertEquals("Colectivo", $tipo);

		$transp = $this->boleto->getLinea();
		$this->assertEquals("146 Rojo", $transp);

		$monto = $this->boleto->getSaldo();
		$this->assertEquals(100, $monto);

		$id = $this->boleto->getId();
		$this->assertEquals(101101, $id);
	}

	public function testTarjetita() {
		// probando cargar la tarjeta con un monto cualquiera
		print ("--Cargando 100:\n");
		$saldoInicial = $this->tarjeta->getSaldo();
		$this->tarjeta->recargar(100);
		$this->assertEquals(100, $this->tarjeta->getSaldo() - $saldoInicial);

		// probando cargar la tarjeta con un monto especial
		print ("--Cargando 290:\n");
		$saldoInicial = $this->tarjeta->getSaldo();
		$this->tarjeta->recargar(290);
		$this->assertEquals(340, $this->tarjeta->getSaldo() - $saldoInicial);

		// probando cargar la tarjeta con otro monto especial
		print ("--Cargando 544:\n");
		$saldoInicial = $this->tarjeta->getSaldo();
		$this->tarjeta->recargar(544);
		$this->assertEquals(680, $this->tarjeta->getSaldo() - $saldoInicial);

		// probando un boleto comun
		print ("--Normal:\n");
		$saldoInicial = $this->tarjeta->getSaldo();
		$this->tarjeta->pagar($this->colectivo, "21-09-2016 16:00");
		$this->assertEquals($saldoInicial-8.5, $this->tarjeta->getSaldo());

		// probando un trasbordo
		print ("--Trasbordo:\n");
		$this->colectivo = new Colectivo("129 Rojo", "Rosario Bus");
		$saldoInicial = $this->tarjeta->getSaldo();
		$this->tarjeta->pagar($this->colectivo, "21-09-2016 16:21");
		$this->assertEquals($saldoInicial-2.81, $this->tarjeta->getSaldo());

		// probando una bicicleta
		print ("--Bicicleta:\n");
		$saldoInicial = $this->tarjeta->getSaldo();
		$this->tarjeta->pagar($this->bicicleta, "21-09-2016 16:50");
		$this->assertEquals($saldoInicial-12, $this->tarjeta->getSaldo());

		// probando el pase libre
		print ("--Libre:\n");
		$this->tarjetaAlternativa = new PaseLibre();
		$this->tarjetaAlternativa->recargar(50);
		$saldoInicial = $this->tarjetaAlternativa->getSaldo();
		$this->tarjetaAlternativa->pagar($this->colectivo, "12-03-2016 18:32");
		$this->assertEquals($saldoInicial, $this->tarjetaAlternativa->getSaldo());

		// probando el medio
		print ("--Medio:\n");
		$this->tarjetaAlternativa = new MedioBoleto();
		$this->tarjetaAlternativa->recargar(50);
		$saldoInicial = $this->tarjetaAlternativa->getSaldo();
		$this->tarjetaAlternativa->pagar($this->colectivo, "12-03-2016 18:32");
		$this->assertEquals($saldoInicial-4.25, $this->tarjetaAlternativa->getSaldo());

		// probando los plus
		print ("--Viaje PLUS 2:\n");
		$this->tarjetaAlternativa = new Tarjetita();
		print ($this->tarjetaAlternativa->getPlus() . " PLUS RESTANTES\n");
		$this->tarjetaAlternativa->recargar(4);
		$saldoInicial = $this->tarjetaAlternativa->getSaldo();
		$this->tarjetaAlternativa->pagar($this->colectivo, "12-03-2016 18:12");
		$this->assertEquals($saldoInicial, $this->tarjetaAlternativa->getSaldo());
		$this->assertEquals(1, $this->tarjetaAlternativa->getPlus());

		// probando que la bici no funcione sin saldo suficiente
		print ($this->tarjetaAlternativa->getPlus() . " PLUS RESTANTES\n");
		print ("--Pagando bici sin saldo:\n");
		$viajesIniciales = $this->tarjetaAlternativa->getViajesRealizados();
		$this->tarjetaAlternativa->pagar($this->bicicleta, "12-03-2016 20:22");
		$this->assertEquals($viajesIniciales, $this->tarjetaAlternativa->getViajesRealizados());

		// probando que no se pueda seguir pagando colectivo luego de 2 plus
		print ($this->tarjetaAlternativa->getPlus() . " PLUS RESTANTES\n");
		print ("--Viaje PLUS #1:\n");
		$this->tarjetaAlternativa->pagar($this->colectivo, "12-03-2016 22:39");
		$this->assertEquals(0, $this->tarjetaAlternativa->getPlus());
		print ("--Viaje Negado:\n");
		$viajesIniciales = $this->tarjetaAlternativa->getViajesRealizados();
		$this->tarjetaAlternativa->pagar($this->colectivo, "12-04-2016 12:18");
		$this->assertEquals($viajesIniciales, $this->tarjetaAlternativa->getViajesRealizados());
	}

}

?>