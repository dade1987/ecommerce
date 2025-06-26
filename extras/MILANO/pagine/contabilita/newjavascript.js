$("[class^='totale_importi_']").each(
                        function () {
                            var negozio = $(this).attr('class').substr(15);

                            var totale_importi = 0.0;
                            $("td:contains('" + negozio + "')").parent().find($('td.importo')).each(function () {
                                if ($(this).html().length !== 0)
                                {
                                    totale_importi = (parseFloat(totale_importi) + parseFloat($(this).html())).toFixed(2);
                                    //console.log(totale_importi);
                                }
                            });
                            $("[class^='totale_importi_" + negozio + "']").html(totale_importi);

                            var totale_residui = 0.0;
                            $("td:contains('" + negozio + "')").parent().find($('td.residuo')).each(function () {
                                if ($(this).html().length !== 0)
                                {
                                    totale_residui = (parseFloat(totale_residui) + parseFloat($(this).html())).toFixed(2);
                                    //console.log("Residuo: "+$(this).html());
                                }
                            });
                            $("[class^='totale_residuo_" + negozio + "']").html(totale_residui);

                            var totale_pagati = 0.0;
                            //console.log(typeof (totale_pagati));
                            $("td:contains('" + negozio + "')").parent().find($('td>input.pagato')).each(function () {
                                if ($(this).val().length !== 0)
                                {
                                    totale_pagati = (parseFloat(totale_pagati) + parseFloat($(this).val())).toFixed(2);
                                }
                            });
                            $("[class^='totale_pagati_" + negozio + "']").html(totale_pagati);
                        });