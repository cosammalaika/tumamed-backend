@props(['status'])

@php
    $map = [
        'PENDING' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300',
        'SENT' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300',
        'ACCEPTED' => 'bg-teal-100 text-teal-800 dark:bg-teal-900/60 dark:text-teal-300',
        'DELIVERING' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/60 dark:text-indigo-300',
        'DELIVERED' => 'bg-green-100 text-green-800 dark:bg-green-900/60 dark:text-green-300',
        'DECLINED' => 'bg-red-100 text-red-800 dark:bg-red-900/60 dark:text-red-300',
        'CANCELLED' => 'bg-red-100 text-red-800 dark:bg-red-900/60 dark:text-red-300',
        'EXPIRED' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
        'FORWARDED' => 'bg-sky-100 text-sky-800 dark:bg-sky-900/60 dark:text-sky-300',
    ];
    $classes = $map[$status] ?? 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300';
@endphp

<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $classes }}">
    {{ $status }}
</span>
