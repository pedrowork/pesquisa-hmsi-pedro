import * as React from 'react';
import { cn } from '@/lib/utils';

export type ChartConfig = Record<
  string,
  {
    label: string;
    color?: string;
  }
>;

export function ChartContainer({
  className,
  children,
}: {
  className?: string;
  children: React.ReactNode;
}) {
  return (
    <div className={cn('w-full h-64 md:h-72 lg:h-80', className)}>
      {children}
    </div>
  );
}

export function ChartTooltipContent({
  active,
  payload,
  label,
}: {
  active?: boolean;
  payload?: any[];
  label?: React.ReactNode;
}) {
  if (!active || !payload || payload.length === 0) {
    return null;
  }

  return (
    <div className="rounded-md border bg-popover px-3 py-2 text-sm text-popover-foreground shadow-md">
      {label && <div className="mb-1 font-medium">{label}</div>}
      <div className="space-y-0.5">
        {payload.map((entry, idx) => {
          const color =
            (entry.color as string) ||
            'hsl(var(--primary))';
          return (
            <div key={idx} className="flex items-center gap-2">
              <span
                className="inline-block size-2 rounded-full"
                style={{ backgroundColor: color }}
              />
              <span className="text-muted-foreground">
                {entry.name}:
              </span>
              <span className="font-medium">
                {typeof entry.value === 'number'
                  ? Number(entry.value).toFixed(2)
                  : entry.value}
              </span>
            </div>
          );
        })}
      </div>
    </div>
  );
}


