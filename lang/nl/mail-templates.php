<?php

return [

    'registration_submitted_payment_pending' => <<<'JSON'
        {
            "type": "doc",
            "content": [
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Beste "
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "name"
                            }
                        },
                        {
                            "type": "text",
                            "text": ","
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "We hebben je inschrijving ontvangen. Om deze definitief te maken is het nodig dat je voor "
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "expires_at"
                            },
                            "marks": [
                                {
                                    "type": "bold"
                                }
                            ]
                        },
                        {
                            "type": "text",
                            "text": " betaalt, anders vervalt je inschrijving weer."
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Klik op onderstaande knop om de huidige status van je inschrijving te zien. Hier vind je ook de betaallink."
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "status_button"
                            }
                        },
                        {
                            "type": "text",
                            "text": " "
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Met vriendelijke groet,"
                        },
                        {
                            "type": "hardBreak"
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "event_title"
                            }
                        }
                    ]
                }
            ]
        }
        JSON,

    'waitlisted' => <<<'JSON'
        {
            "type": "doc",
            "content": [
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Beste "
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "name"
                            }
                        },
                        {
                            "type": "text",
                            "text": ","
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "We hebben je inschrijving ontvangen. Helaas zijn er geen plekken meer beschikbaar en ben je op de wachtlijst geplaats. Als er plekken beschikbaar komen nemen we contact met je op."
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Klik op onderstaande knop om de huidige status van je inschrijving te zien."
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "status_button"
                            }
                        },
                        {
                            "type": "text",
                            "text": " "
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Met vriendelijke groet,"
                        },
                        {
                            "type": "hardBreak"
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "event_title"
                            }
                        }
                    ]
                }
            ]
        }
        JSON,

    'invited_from_waitlist' => <<<'JSON'
        {
            "type": "doc",
            "content": [
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Beste "
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "name"
                            }
                        },
                        {
                            "type": "text",
                            "text": ","
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Er is een plek beschikbaar gekomen voor "
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "event_title"
                            }
                        },
                        {
                            "type": "text",
                            "text": ". Je hebt tot "
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "expires_at"
                            },
                            "marks": [
                                {
                                    "type": "bold"
                                }
                            ]
                        },
                        {
                            "type": "text",
                            "text": " om te betalen, daarna vervalt je inschrijving."
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Klik op onderstaande knop om de huidige status van je inschrijving te zien. Hier vind je ook de betaallink."
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "status_button"
                            }
                        },
                        {
                            "type": "text",
                            "text": " "
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Met vriendelijke groet,"
                        },
                        {
                            "type": "hardBreak"
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "event_title"
                            }
                        }
                    ]
                }
            ]
        }
        JSON,

    'registration_completed' => <<<'JSON'
        {
            "type": "doc",
            "content": [
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Beste "
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "name"
                            }
                        },
                        {
                            "type": "text",
                            "text": ","
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Je betaling is ontvangen en je inschrijving is voltooid. Hierbij ontvang je een bevestiging van je inschrijving. Je hebt je ingeschreven met de volgende gegevens:"
                        },
                        {
                            "type": "hardBreak"
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "registration_details"
                            }
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "De prijsdetails zijn als volgt:"
                        },
                        {
                            "type": "hardBreak"
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "pricing_details"
                            }
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Klik op onderstaande knop om de huidige status van je inschrijving te zien."
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "status_button"
                            }
                        },
                        {
                            "type": "text",
                            "text": " "
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Met vriendelijke groet,"
                        },
                        {
                            "type": "hardBreak"
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "event_title"
                            }
                        }
                    ]
                }
            ]
        }
        JSON,

    'invited_to_register' => <<<'JSON'
        {
            "type": "doc",
            "content": [
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Beste "
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "name"
                            }
                        },
                        {
                            "type": "text",
                            "text": ","
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Je bent uitgenodigd voor de voorinschrijving van "
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "event_title"
                            }
                        },
                        {
                            "type": "text",
                            "text": ". "
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "expires_at_sentence"
                            }
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Klik op onderstaande knop om je in te schrijven:"
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "register_button"
                            }
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Let op: deze uitnodiging is persoonlijk en kan maar één keer worden gebruikt."
                        }
                    ]
                },
                {
                    "type": "paragraph",
                    "attrs": {
                        "textAlign": "start"
                    },
                    "content": [
                        {
                            "type": "text",
                            "text": "Met vriendelijke groet,"
                        },
                        {
                            "type": "hardBreak"
                        },
                        {
                            "type": "mergeTag",
                            "attrs": {
                                "id": "event_title"
                            }
                        }
                    ]
                }
            ]
        }
        JSON,

];
