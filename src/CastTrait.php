<?php
namespace Formapro\Values;

trait CastTrait
{
    /**
     * @param mixed $value
     * @param string $castTo
     * 
     * @return mixed
     */
    protected function cast($value, $castTo)
    {
        if (\DateTime::class == $castTo) {
            if (null === $value) {
                return null;
            } elseif (is_numeric($value)) {
                $value = \DateTime::createFromFormat('U', $value);
            } elseif (is_array($value)) {
                if (isset($value['tz'])) {
                    $value = \DateTime::createFromFormat('Y-m-d\TH:i:s', $value['time'], new \DateTimeZone($value['tz']));
                } else {
                    // bc
                    $value = \DateTime::createFromFormat('U', $value['unix']);
                }
            } else {
                $value = new \DateTime($value);
            }
        } else if (\DateInterval::class == $castTo) {
            if (null === $value) {
                return null;
            } elseif (is_array($value)) {
                $value = new \DateInterval($value['interval']);
            } else {
                $value = new \DateInterval($value);
            }
        } else if (\DateTimeZone::class == $castTo) {
            if (null === $value) {
                return null;
            } else {
                $value = new \DateTimeZone($value['tz']);
            }
        } else {
            settype($value, $castTo);
        }
        
        return $value;
    }

    /**
     * @param mixed $value
     * 
     * @return mixed 
     */
    protected function castValue($value)
    {
        if ($value instanceof \DateTime) {
            $value = [
                'unix' => (int) $value->format('U'),
                'time' => (string) $value->format('Y-m-d\TH:i:s'),
                'tz' => $value->getTimezone()->getName(),
            ];
        } elseif ($value instanceof \DateInterval) {
            $value = [
                'interval' => $value->format('P%yY%mM%dDT%HH%IM%SS'),
                'days' => $value->days,
                'y' => $value->y,
                'm' => $value->m,
                'd' => $value->d,
                'h' => $value->h,
                'i' => $value->i,
                's' => $value->s,
            ];
        } elseif ($value instanceof \DateTimeZone) {
            $value = [
                'tz' => $value->getName(),
            ];
        }
        
        return $value;
    }
}