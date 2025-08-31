<?php

namespace core\utils;

class Facing {

	public const FLAG_AXIS_POSITIVE = 1;

	/* most significant 2 bits = axis, least significant bit = is positive direction */
	public const DOWN = Axis::Y << 1;
	public const UP = (Axis::Y << 1) | self::FLAG_AXIS_POSITIVE;
	public const NORTH = Axis::Z << 1;
	public const SOUTH = (Axis::Z << 1) | self::FLAG_AXIS_POSITIVE;
	public const WEST = Axis::X << 1;
	public const EAST = (Axis::X << 1) | self::FLAG_AXIS_POSITIVE;

	public const ALL = [
		self::DOWN,
		self::UP,
		self::NORTH,
		self::SOUTH,
		self::WEST,
		self::EAST,
	];

	public const HORIZONTAL = [
		self::NORTH,
		self::SOUTH,
		self::WEST,
		self::EAST,
	];

	private const CLOCKWISE = [
		Axis::Y => [
			self::NORTH => self::EAST,
			self::EAST => self::SOUTH,
			self::SOUTH => self::WEST,
			self::WEST => self::NORTH,
		],
		Axis::Z => [
			self::UP => self::EAST,
			self::EAST => self::DOWN,
			self::DOWN => self::WEST,
			self::WEST => self::UP,
		],
		Axis::X => [
			self::UP => self::NORTH,
			self::NORTH => self::DOWN,
			self::DOWN => self::SOUTH,
			self::SOUTH => self::UP,
		],
	];

	/**
	 * Returns the axis of the given direction.
	 */
	public static function axis(int $direction): int {
		return $direction >> 1; //shift off positive/negative bit
	}

	/**
	 * Returns whether the direction is facing the positive of its axis.
	 */
	public static function isPositive(int $direction): bool {
		return ($direction & self::FLAG_AXIS_POSITIVE) === self::FLAG_AXIS_POSITIVE;
	}

	/**
	 * Returns the opposite Facing of the specified one.
	 *
	 * @param int $direction 0-5 one of the Facing::* constants
	 */
	public static function opposite(int $direction): int {
		return $direction ^ self::FLAG_AXIS_POSITIVE;
	}

	/**
	 * Rotates the given direction around the axis.
	 *
	 * @throws \InvalidArgumentException if not possible to rotate $direction around $axis
	 */
	public static function rotate(int $direction, int $axis, bool $clockwise): int {
		if (!isset(self::CLOCKWISE[$axis])) {
			throw new \InvalidArgumentException("Invalid axis $axis");
		}
		if (!isset(self::CLOCKWISE[$axis][$direction])) {
			throw new \InvalidArgumentException("Cannot rotate direction $direction around axis $axis");
		}

		$rotated = self::CLOCKWISE[$axis][$direction];
		return $clockwise ? $rotated : self::opposite($rotated);
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public static function rotateY(int $direction, bool $clockwise): int {
		return self::rotate($direction, Axis::Y, $clockwise);
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public static function rotateZ(int $direction, bool $clockwise): int {
		return self::rotate($direction, Axis::Z, $clockwise);
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public static function rotateX(int $direction, bool $clockwise): int {
		return self::rotate($direction, Axis::X, $clockwise);
	}

	/**
	 * Validates the given integer as a Facing direction.
	 *
	 * @throws \InvalidArgumentException if the argument is not a valid Facing constant
	 */
	public static function validate(int $facing): void {
		if (!in_array($facing, self::ALL, true)) {
			throw new \InvalidArgumentException("Invalid direction $facing");
		}
	}
}
