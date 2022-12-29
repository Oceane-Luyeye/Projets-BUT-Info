public abstract class Operation extends Expression {

    // variables d'instance
   private  Expression operande1;
   private  Expression operande2;

    //Constructeur champ à champ
    public Operation(Expression operande1, Expression operande2){
        this.operande1 = operande1;
        this.operande2 = operande2;
    }   

    // retourne la premiere operande de loperation
    public Expression getOperande1(){
        return this.operande1;
        }

    // retourne la seconde operande de loperation
    public Expression getOperande2(){
            return this.operande2;
            }

   public abstract int valeur();
    
}
