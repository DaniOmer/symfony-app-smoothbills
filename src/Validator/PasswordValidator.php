<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PasswordValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        /* @var Password $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        if (!preg_match('/[A-Z]/', $value) || !preg_match('/[0-9]/', $value) || !preg_match('/[a-zA-Z0-9]/', $value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
