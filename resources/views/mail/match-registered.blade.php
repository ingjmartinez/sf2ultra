<x-mail::message>
# Match registrado

**{{ $match->ganador->nombre }}** le ganó a **{{ $match->perdedor->nombre }}**

<x-mail::table>
| Ganador | Perdedor | Resultado | Fecha |
| :------ | :------- | :-------- | :---- |
| {{ $match->ganador->nombre }} | {{ $match->perdedor->nombre }} | {{ $match->victorias_ganador }} - {{ $match->victorias_perdedor }} | {{ $match->fecha->format('d/m/Y H:i') }} |
</x-mail::table>

<x-mail::button :url="route('dashboard', ['season' => $match->fecha->year])">
Ver estadísticas
</x-mail::button>

{{ config('app.name') }}
</x-mail::message>
