<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class EscapeCharacterValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        /* @var EscapeCharacter $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        $escapedValue = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        if ($value !== $escapedValue) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
