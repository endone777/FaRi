<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

/**
 * Guest shopping cart kept in the session: ordering never requires an account.
 *
 * Headlights are sold as a pair, so a line is one product and a quantity of sets.
 *
 * @phpstan-type CartLine array{key: string, product_id: int, qty: int}
 */
class Cart
{
    private const KEY = 'cart';

    public function __construct(private readonly Session $session) {}

    /**
     * Raw cart lines as stored in the session.
     *
     * @return array<string, CartLine>
     */
    public function lines(): array
    {
        /** @var array<string, CartLine> $lines */
        $lines = $this->session->get(self::KEY, []);

        return $lines;
    }

    public function add(Product $product, int $qty = 1): void
    {
        $key = $this->key($product->id);
        $lines = $this->lines();

        $lines[$key] = [
            'key' => $key,
            'product_id' => $product->id,
            'qty' => min(99, ($lines[$key]['qty'] ?? 0) + max(1, $qty)),
        ];

        $this->session->put(self::KEY, $lines);
    }

    public function setQty(string $key, int $qty): void
    {
        $lines = $this->lines();

        if (! isset($lines[$key])) {
            return;
        }

        if ($qty < 1) {
            unset($lines[$key]);
        } else {
            $lines[$key]['qty'] = min(99, $qty);
        }

        $this->session->put(self::KEY, $lines);
    }

    public function remove(string $key): void
    {
        $lines = $this->lines();
        unset($lines[$key]);

        $this->session->put(self::KEY, $lines);
    }

    public function clear(): void
    {
        $this->session->forget(self::KEY);
    }

    /**
     * Cart lines joined with their products, dropping lines whose product is gone.
     *
     * @return Collection<int, array{key: string, qty: int, product: Product, line_total: int}>
     */
    public function detailed(): Collection
    {
        $lines = $this->lines();

        if ($lines === []) {
            return collect();
        }

        $products = Product::query()
            ->whereIn('id', array_column($lines, 'product_id'))
            ->get()
            ->keyBy('id');

        $detailed = collect($lines)
            ->filter(fn (array $line): bool => $products->has($line['product_id']))
            ->map(function (array $line) use ($products): array {
                /** @var Product $product */
                $product = $products->get($line['product_id']);

                return [
                    'key' => $line['key'],
                    'qty' => $line['qty'],
                    'product' => $product,
                    'line_total' => $product->price * $line['qty'],
                ];
            })
            ->values();

        if ($detailed->count() !== count($lines)) {
            $this->session->put(
                self::KEY,
                $detailed->mapWithKeys(fn (array $line): array => [$line['key'] => [
                    'key' => $line['key'],
                    'product_id' => $line['product']->id,
                    'qty' => $line['qty'],
                ]])->all(),
            );
        }

        return $detailed;
    }

    public function count(): int
    {
        return array_sum(array_column($this->lines(), 'qty'));
    }

    public function itemsTotal(): int
    {
        return (int) $this->detailed()->sum('line_total');
    }

    public function weight(): float
    {
        return (float) $this->detailed()->sum(
            fn (array $line): float => $line['product']->weight * $line['qty'],
        );
    }

    public function isEmpty(): bool
    {
        return $this->lines() === [];
    }

    private function key(int $productId): string
    {
        return 'p'.$productId;
    }
}
