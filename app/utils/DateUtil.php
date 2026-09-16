<?php

class DateUtil
{
    /**
     * Converte data ISO (Y-m-d) para formato brasileiro (d/m/Y).
     */
    public static function toBr(?string $date): string
    {
        if (!$date) return '—';
        $dt = DateTime::createFromFormat('Y-m-d', $date);
        return $dt ? $dt->format('d/m/Y') : $date;
    }

    /**
     * Converte data ISO com hora para formato legível em PT-BR.
     * Ex: 2024-01-15 14:30:00 → 15/01/2024 14:30
     */
    public static function toBrFull(?string $datetime): string
    {
        if (!$datetime) return '—';
        $dt = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
        return $dt ? $dt->format('d/m/Y H:i') : $datetime;
    }

    /**
     * Retorna quantos dias passaram desde uma data.
     * Ex: 5 dias atrás
     */
    public static function daysAgo(string $date): string
    {
        $dt   = new DateTime($date);
        $now  = new DateTime();
        $diff = $now->diff($dt);

        if ($diff->days === 0) return 'hoje';
        if ($diff->days === 1) return 'ontem';
        return $diff->days . ' dias atrás';
    }

    /**
     * Retorna a data de hoje no formato Y-m-d.
     */
    public static function today(): string
    {
        return date('Y-m-d');
    }

    /**
     * Retorna o timestamp atual formatado para log.
     */
    public static function now(): string
    {
        return date('Y-m-d H:i:s');
    }
}


