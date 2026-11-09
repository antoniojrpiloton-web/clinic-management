<?php

namespace Database\Seeders;

use App\Models\DiseaseCategory;
use Illuminate\Database\Seeder;

class DiseaseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [

            [
                'code' => 'COMM-TB',
                'name' => 'Tuberculosis',
                'classification' => DiseaseCategory::TYPE_COMMUNICABLE,
                'keywords' => 'tuberculosis,ptb,tb ,tb-,pulmonary tb,koch',
                'sort_order' => 10,
                'is_active' => true,
            ],
            [
                'code' => 'COMM-URTI',
                'name' => 'URTI (cough, colds, fever, runny nose, sore throat and tonsilitis)',
                'classification' => DiseaseCategory::TYPE_COMMUNICABLE,
                'keywords' => 'urti,upper respiratory,cough,cold,colds,fever,pyrexia,runny nose,rhinitis,coryza,sore throat,pharyngitis,tonsillitis,tonsilitis,influenza,flu,bronchitis,pneumonia',
                'sort_order' => 20,
                'is_active' => true,
            ],
            [
                'code' => 'COMM-SVI',
                'name' => 'SVI (Chickenpox, Conjunctivitis, Dengue, Herpes Zoster)',
                'classification' => DiseaseCategory::TYPE_COMMUNICABLE,
                'keywords' => 'svi,chickenpox,varicella,conjunctivitis,pink eye,sore eyes,dengue,herpes,shingles,zoster,measles,mumps,rubella,viral exanthem',
                'sort_order' => 30,
                'is_active' => true,
            ],

            [
                'code' => 'NCD-DISABILITY',
                'name' => 'Illness of Student of Disabiltiy',
                'classification' => DiseaseCategory::TYPE_NON_COMMUNICABLE,
                'keywords' => 'disabilit,disabiltiy,pwd,special needs,orthopedic impairment,mobility assistance',
                'sort_order' => 10,
                'is_active' => true,
            ],
            [
                'code' => 'NCD-REPRO',
                'name' => 'Reproductive (Pregnancy, Cyst and Dysmenorrhea)',
                'classification' => DiseaseCategory::TYPE_NON_COMMUNICABLE,
                'keywords' => 'reproductive,pregnan,cyst,pcos,ovarian,dysmenorrhea,menstrual,amenorrhea,menorrhagia',
                'sort_order' => 20,
                'is_active' => true,
            ],
            [
                'code' => 'NCD-GI',
                'name' => 'Gastrointestinal (Gallstones, Ulcer,Hemorrhoids, abdominal pain, bloating, diarrhea, constipation, nausea, vomiting, and blood in the stool)',
                'classification' => DiseaseCategory::TYPE_NON_COMMUNICABLE,
                'keywords' => 'gastro,gallstone,cholelithiasis,ulcer,peptic,gastritis,gerd,hyperacidity,dyspepsia,hemorrhoid,abdominal pain,stomach,epigastric,bloating,diarrhea,loose watery,constipation,nausea,vomiting,blood in the stool,hematochezia,melena',
                'sort_order' => 30,
                'is_active' => true,
            ],
            [
                'code' => 'NCD-SKIN',
                'name' => 'Skin and Subcutaneous Tissue (Dermatitis, Rashes)',
                'classification' => DiseaseCategory::TYPE_NON_COMMUNICABLE,
                'keywords' => 'dermatitis,rash,rashes,eczema,urticaria,hives,pruritus,allergy,allergic,skin,cellulitis,abscess,boil,folliculitis,insect bite',
                'sort_order' => 40,
                'is_active' => true,
            ],
            [
                'code' => 'NCD-GU',
                'name' => 'Genitourinary (UTI and painful Urination)',
                'classification' => DiseaseCategory::TYPE_NON_COMMUNICABLE,
                'keywords' => 'uti,urinary tract,dysuria,painful urination,hematuria,renal,kidney,genitourinary',
                'sort_order' => 50,
                'is_active' => true,
            ],
            [
                'code' => 'NCD-NEURO',
                'name' => 'Neurological ( Epilepsy, Blurring of vision, Ear pain, Epistaxis, Headache, Vertigo, dizziness, fainting, muscle weakness, numbness, tremors, vision changes, insomnia, migraine)',
                'classification' => DiseaseCategory::TYPE_NON_COMMUNICABLE,
                'keywords' => 'neurolog,epilepsy,seizure,blurring of vision,vision,ear pain,otalgia,otitis,epistaxis,nosebleed,headache,cephalalgia,vertigo,dizziness,dizzy,faint,syncope,muscle weakness,numbness,tremor,insomnia,migraine',
                'sort_order' => 60,
                'is_active' => true,
            ],
            [
                'code' => 'NCD-MSK',
                'name' => 'Muskoskeletal and Connective Tissue ( Arthritis, Back/ Neck Pain, Injury, Fracture, Wound, Burns, Swelling)',
                'classification' => DiseaseCategory::TYPE_NON_COMMUNICABLE,
                'keywords' => 'musculo,muskoskeletal,arthritis,gout,back pain,neck pain,myalgia,muscle spasm,strain,sprain,injury,trauma,fracture,dislocation,wound,laceration,abrasion,puncture,contusion,bruise,burn,swelling',
                'sort_order' => 70,
                'is_active' => true,
            ],
            [
                'code' => 'NCD-MENTAL',
                'name' => 'Mental and Behavioral (Depression and Anxiety)',
                'classification' => DiseaseCategory::TYPE_NON_COMMUNICABLE,
                'keywords' => 'mental,behavioral,depression,depressive,anxiety,panic,hyperventilation,stress,psych',
                'sort_order' => 80,
                'is_active' => true,
            ],
            [
                'code' => 'NCD-ENDO',
                'name' => 'Endocrine, Nutritional and Metabolic ( Obesity, Malnutrition and Anorexia)',
                'classification' => DiseaseCategory::TYPE_NON_COMMUNICABLE,
                'keywords' => 'endocrine,nutritional,metabolic,obesity,overweight,malnutrition,underweight,anorexia,diabetes,hyperglycemia,hypoglycemia,thyroid,goiter,anemia',
                'sort_order' => 90,
                'is_active' => true,
            ],
            [
                'code' => 'NCD-RESP',
                'name' => 'Respiratory (Asthma and Dyspnea)',
                'classification' => DiseaseCategory::TYPE_NON_COMMUNICABLE,
                'keywords' => 'asthma,dyspnea,shortness of breath,wheez,bronchospasm,difficulty of breathing',
                'sort_order' => 100,
                'is_active' => true,
            ],
            [
                'code' => 'NCD-CARDIO',
                'name' => 'Cardiovascular (Heart Disease, Hypertension, Chest Pain, Elevated BP)',
                'classification' => DiseaseCategory::TYPE_NON_COMMUNICABLE,
                'keywords' => 'cardiovascular,cardiac,heart disease,hypertension,high blood,chest pain,angina,elevated bp,elevated blood pressure,palpitation,tachycardia',
                'sort_order' => 110,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            DiseaseCategory::updateOrCreate(
                ['code' => $category['code']],
                $category
            );
        }
    }
}
