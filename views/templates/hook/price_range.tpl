
<div id="price_range" style="display:inline-block;">
  <p>Количество товара в диапазоне</p>
  {if $PRICE_RANGE_PRICE1}
  <span> от {$PRICE_RANGE_PRICE1} руб.</span>
  {/if}
  {if $PRICE_RANGE_PRICE2}
  <span> до {$PRICE_RANGE_PRICE2} руб.</span>
  {/if}
   {if $PRICE_RANGE_COUNT}
  <span> - {$PRICE_RANGE_COUNT} шт.</span>
  {/if}
</div>
