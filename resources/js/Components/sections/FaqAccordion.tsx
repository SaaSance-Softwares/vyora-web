import React, { useState } from 'react';
import { ChevronDown } from 'lucide-react';

interface Faq {
    id: number;
    question: string;
    answer: string;
}

interface Props {
    faqs: Faq[];
}

export function FaqAccordion({ faqs }: Props) {
    if (!faqs || faqs.length === 0) return null;

    const [openIndex, setOpenIndex] = useState<number | null>(null);

    const toggleFaq = (index: number) => {
        setOpenIndex(openIndex === index ? null : index);
    };

    return (
        <div className="max-w-3xl mx-auto px-4 py-16">
            <div className="text-center mb-10">
                <h2 className="text-3xl font-black uppercase tracking-tight text-gray-900 mb-4">Frequently Asked Questions</h2>
                <div className="w-24 h-1 bg-black mx-auto"></div>
            </div>
            
            <div className="space-y-4">
                {faqs.map((faq, index) => (
                    <div 
                        key={faq.id} 
                        className={`border rounded-2xl overflow-hidden transition-all duration-300 ${openIndex === index ? 'border-black shadow-md bg-white' : 'border-gray-200 bg-gray-50 hover:bg-gray-100'}`}
                    >
                        <button
                            onClick={() => toggleFaq(index)}
                            className="w-full flex items-center justify-between p-5 md:p-6 text-left focus:outline-none"
                            aria-expanded={openIndex === index}
                        >
                            <span className={`text-base md:text-lg font-bold pr-8 transition-colors ${openIndex === index ? 'text-black' : 'text-gray-800'}`}>
                                {faq.question}
                            </span>
                            <ChevronDown 
                                className={`w-5 h-5 flex-shrink-0 text-gray-500 transition-transform duration-300 ${openIndex === index ? 'rotate-180 text-black' : ''}`} 
                            />
                        </button>
                        
                        <div 
                            className={`overflow-hidden transition-all duration-300 ease-in-out ${openIndex === index ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0'}`}
                        >
                            <div className="p-5 md:p-6 pt-0 text-gray-600 prose prose-sm max-w-none border-t border-gray-100/0">
                                <div dangerouslySetInnerHTML={{ __html: faq.answer.replace(/\n/g, '<br />') }} />
                            </div>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}
