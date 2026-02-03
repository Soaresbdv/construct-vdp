<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        // Lista de imagens bonitas de arquitetura do Unsplash para não ficar feio
        $images = [
            'https://images.unsplash.com/photo-1600596542815-2495db98dada?auto=format&fit=crop&q=80&w=800',
            'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?auto=format&fit=crop&q=80&w=800',
            'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&q=80&w=800',
            'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&q=80&w=800',
            'https://images.unsplash.com/photo-1613545325278-f24b0cae1224?auto=format&fit=crop&q=80&w=800',
        ];

        return [
            'title' => 'Edifício ' . fake()->city(),
            'location' => fake()->city() . ', ' . fake()->stateAbbr(),
            'status' => fake()->randomElement(['planning', 'in_progress', 'finished']),
            'image_url' => fake()->randomElement($images),
            'start_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'end_date' => fake()->dateTimeBetween('now', '+2 years'),
        ];
    }
}