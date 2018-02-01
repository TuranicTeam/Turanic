<?php

/*
 * RakLib network library
 *
 *
 * This project is not affiliated with Jenkins Software LLC nor RakNet.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 */

declare(strict_types=1);

namespace raklib\server;

use raklib\utils\InternetAddress;

class UDPServerSocket{
	/** @var resource */
	protected $socket;
    /** @var InternetAddress */
    private $bindAddress;

    public function __construct(InternetAddress $bindAddress){
        $this->bindAddress = $bindAddress;
        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if(@socket_bind($this->socket, $bindAddress->ip, $bindAddress->port) === true){
            socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 0);
            $this->setSendBuffer(1024 * 1024 * 8)->setRecvBuffer(1024 * 1024 * 8);
        }else{
            throw new \InvalidStateException("Failed to bind to " . $bindAddress . ": " . trim(socket_strerror(socket_last_error($this->socket))));
        }
        socket_set_nonblock($this->socket);
    }

    /**
     * @return InternetAddress
     */
    public function getBindAddress() : InternetAddress{
        return $this->bindAddress;
    }

    /**
     * @return resource
     */
    public function getSocket(){
		return $this->socket;
	}

	public function close(){
		socket_close($this->socket);
	}

	public function readPacket(?string &$buffer, ?string &$source, ?int &$port){
		return socket_recvfrom($this->socket, $buffer, 65535, 0, $source, $port);
	}

	public function writePacket(string $buffer, string $dest, int $port){
		return socket_sendto($this->socket, $buffer, strlen($buffer), 0, $dest, $port);
	}

	/**
	 * @param int $size
	 *
	 * @return $this
	 */
	public function setSendBuffer(int $size){
		@socket_set_option($this->socket, SOL_SOCKET, SO_SNDBUF, $size);

		return $this;
	}

	/**
	 * @param int $size
	 *
	 * @return $this
	 */
	public function setRecvBuffer(int $size){
		@socket_set_option($this->socket, SOL_SOCKET, SO_RCVBUF, $size);

		return $this;
	}

}
