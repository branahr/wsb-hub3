# Woocommerce WSB HUB3 plugin

WSB HUB3 je dodatak za WooCommerce koji prikazuje sve potrebne podatke za uplatu putem hrvatskih banaka, uključujući i 2D barkod za skeniranje. Podatke je moguće prikazati kao običan text/html ili kao popunjenu uplatnicu HUB-3A. Podaci su vidljivi na stranici zahvale, stranici s detaljima o narudžbi, te se šalju i u obavijesnom emailu. Prikazuju se samo ako je kao način plaćanja odabrana “Direktna bankovna transakcija”. Ako prodajete i izvan Hrvatske, moguće je u postavkama dodatka ograničiti kreiranje i prikazivanje ovih podataka samo na kupce iz Hrvatske. Dodatak koristi bigfish.software API za generiranje barkoda: https://hub3.bigfish.software/

## Zahtjevi

    - PHP verzija 5.6 ili više
    - GD instaliran na poslužitelju
    - WordPress verzija 5.0 i više
    - Woocommerce plugin instaliran i uključen (v 4.0 ili veća)
    - Metoda plaćanja “Direktna bankovna transakcija” uključena
    - EUR kao zadana valuta trgovine

## Dokumentacija

Dokumentacija i detaljnije upute nalaze se na stranici https://www.webstudiobrana.com/hr/wsb-hub3-generator

Službena stranica plugina na WordPress repozitoriju: https://hr.wordpress.org/plugins/wsb-hub3/

## Značajke:

    - Podrška za pluginove za sekvencijske brojeve narudžbi
    - Mogućnost dodavanja više IBAN-a (banaka)
    - Mogućnost odabira načina prikaza za detalje plaćanja
    - Barkod u GIF, JPG ili PNG formatu
    - Podešavajući uzorak za kreiranje poziva na broj
    - Detalji za plaćanje na stranici zahvale i u detaljima narudžbe
    - Barkod u vašoj omiljenoj boji
    - Detalji za plaćanje prikazuju se samo kupcima iz Hrvatske
    - U opisu plaćanja možete koristiti [order] za generiranje broja narudžbe
    - Kratki kodovi za prikaz barkoda i/ili uplatnice na prilagođenim stranicama zahvale.

## Uzorak poziva na broj

Kao poziv na broj možete odabrati jedan od sljedećih uzoraka:
– narudžba (može biti prilagođeni broj generiran iz drugog plugina)
– datum
– narudžba-datum
– datum-narudžba

Ako koristite datum u pozivu na broj, možete ga postaviti u nekom od sljedećih formata:
– ddmmgggg
– ddmmgg
– ddmm
– mmgggg
– mmgg
– gggg
– gg

Također možete dodati sufiks i/ili prefiks pozivu na broj (do 6 znamenki svaki).