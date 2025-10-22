from __future__ import annotations

from dataclasses import dataclass
from typing import Iterable, List, Optional

import pandas as pd


@dataclass
class BehaviourInsight:
    title: str
    value: str
    context: Optional[str] = None


class BehaviourAnalyser:
    """Generates behaviour insights across multiple petty-cash transactions."""

    def summarise(self, records: Iterable[dict]) -> dict:
        df = pd.DataFrame(list(records))

        if df.empty:
            return {
                "insights": [],
                "series": {},
            }

        df["category"] = df.get("category", "uncategorised")
        df["amount"] = df.get("amount", 0.0).astype(float)
        df["count"] = 1

        category_totals = df.groupby("category")["amount"].sum().sort_values(ascending=False)
        monthly_totals = df.groupby("month")["amount"].sum().sort_index() if "month" in df.columns else pd.Series(dtype=float)

        insights: List[BehaviourInsight] = []
        if not category_totals.empty:
            top_category = category_totals.idxmax()
            insights.append(
                BehaviourInsight(
                    title="بیشترین هزینه",
                    value=top_category,
                    context=f"{category_totals.max():,.0f} ریال",
                )
            )

        if not monthly_totals.empty:
            growth = monthly_totals.pct_change().dropna()
            if not growth.empty:
                last_growth = growth.iloc[-1]
                direction = "افزایشی" if last_growth > 0 else "کاهشی"
                insights.append(
                    BehaviourInsight(
                        title="روند ماهانه",
                        value=direction,
                        context=f"{last_growth:+.1%}",
                    )
                )

        return {
            "insights": [insight.__dict__ for insight in insights],
            "series": {
                "categories": category_totals.to_dict(),
                "monthly": monthly_totals.to_dict(),
            },
        }
